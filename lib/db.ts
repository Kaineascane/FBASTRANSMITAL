import fs from 'fs';
import path from 'path';
import type { DetailRow, TransmittalInput, TransmittalRow } from './transmittal-types';

export type DbMode = 'postgres' | 'sqlite';

export function getDbMode(): DbMode {
  if (process.env.POSTGRES_URL) return 'postgres';
  return 'sqlite';
}

export function isDatabaseReady(): boolean {
  if (process.env.POSTGRES_URL) return true;
  if (process.env.NODE_ENV !== 'production') return true;
  return false;
}

function sqlitePath(): string {
  const dir = path.join(process.cwd(), 'data');
  if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
  return path.join(dir, 'transmittal.db');
}

async function getSqlite() {
  if (process.env.VERCEL && !process.env.POSTGRES_URL) {
    throw new Error('POSTGRES_URL is required on Vercel. Add Postgres storage in your project settings.');
  }
  const Database = (await import('better-sqlite3')).default;
  return new Database(sqlitePath());
}

async function getPostgresSql() {
  const { sql } = await import('@vercel/postgres');
  return sql;
}

export async function ensureSchema(): Promise<void> {
  if (getDbMode() === 'postgres') {
    const sql = await getPostgresSql();
    await sql`
      CREATE TABLE IF NOT EXISTS tbl_transmittal (
        id SERIAL PRIMARY KEY,
        from_branch VARCHAR(100) NOT NULL,
        to_branch VARCHAR(100) NOT NULL,
        released_by VARCHAR(100) NOT NULL,
        date_released DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `;
    await sql`
      CREATE TABLE IF NOT EXISTS tbl_transmittal_details (
        id SERIAL PRIMARY KEY,
        transmittal_id INT NOT NULL REFERENCES tbl_transmittal(id) ON DELETE CASCADE,
        pad_no INT NOT NULL,
        si_start BIGINT NOT NULL,
        si_end BIGINT NOT NULL
      )
    `;
    await sql`CREATE INDEX IF NOT EXISTS idx_transmittal_date ON tbl_transmittal(date_released)`;
    await sql`CREATE INDEX IF NOT EXISTS idx_details_transmittal ON tbl_transmittal_details(transmittal_id)`;
    return;
  }

  const db = await getSqlite();
  db.exec(`
    CREATE TABLE IF NOT EXISTS tbl_transmittal (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      from_branch TEXT NOT NULL,
      to_branch TEXT NOT NULL,
      released_by TEXT NOT NULL,
      date_released TEXT NOT NULL,
      created_at TEXT DEFAULT (datetime('now'))
    );
    CREATE TABLE IF NOT EXISTS tbl_transmittal_details (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      transmittal_id INTEGER NOT NULL,
      pad_no INTEGER NOT NULL,
      si_start INTEGER NOT NULL,
      si_end INTEGER NOT NULL,
      FOREIGN KEY (transmittal_id) REFERENCES tbl_transmittal(id) ON DELETE CASCADE
    );
    CREATE INDEX IF NOT EXISTS idx_transmittal_date ON tbl_transmittal(date_released);
    CREATE INDEX IF NOT EXISTS idx_details_transmittal ON tbl_transmittal_details(transmittal_id);
  `);
  db.close();
}

export async function dbGetNextSeries(): Promise<{ pad: number; si: number }> {
  await ensureSchema();
  let pad = 1;
  let si = 1;

  if (getDbMode() === 'postgres') {
    const sql = await getPostgresSql();
    const padResult = await sql`SELECT MAX(pad_no) AS last_pad FROM tbl_transmittal_details`;
    const siResult = await sql`SELECT MAX(si_end) AS last_si FROM tbl_transmittal_details`;
    if (padResult.rows[0]?.last_pad != null) pad = Number(padResult.rows[0].last_pad) + 1;
    if (siResult.rows[0]?.last_si != null) si = Number(siResult.rows[0].last_si) + 1;
    return { pad, si };
  }

  const db = await getSqlite();
  const lastPad = db.prepare('SELECT MAX(pad_no) AS v FROM tbl_transmittal_details').get() as { v: number | null };
  const lastSi = db.prepare('SELECT MAX(si_end) AS v FROM tbl_transmittal_details').get() as { v: number | null };
  db.close();
  if (lastPad?.v != null) pad = lastPad.v + 1;
  if (lastSi?.v != null) si = lastSi.v + 1;
  return { pad, si };
}

export async function dbSaveTransmittal(input: TransmittalInput, siPerPad: number): Promise<number> {
  await ensureSchema();

  if (getDbMode() === 'postgres') {
    const sql = await getPostgresSql();
    const header = await sql`
      INSERT INTO tbl_transmittal (from_branch, to_branch, released_by, date_released)
      VALUES (${input.from_branch}, ${input.to_branch}, ${input.released_by}, ${input.date_released})
      RETURNING id
    `;
    const transmittalId = Number(header.rows[0].id);
    let pad = input.starting_pad;
    let siStart = input.starting_si;
    for (let i = 0; i < input.total_pads; i++) {
      const siEnd = siStart + siPerPad - 1;
      await sql`
        INSERT INTO tbl_transmittal_details (transmittal_id, pad_no, si_start, si_end)
        VALUES (${transmittalId}, ${pad}, ${siStart}, ${siEnd})
      `;
      pad++;
      siStart = siEnd + 1;
    }
    return transmittalId;
  }

  const db = await getSqlite();
  const insert = db.prepare(`
    INSERT INTO tbl_transmittal (from_branch, to_branch, released_by, date_released)
    VALUES (?, ?, ?, ?)
  `);
  const detail = db.prepare(`
    INSERT INTO tbl_transmittal_details (transmittal_id, pad_no, si_start, si_end)
    VALUES (?, ?, ?, ?)
  `);

  const run = db.transaction(() => {
    const r = insert.run(input.from_branch, input.to_branch, input.released_by, input.date_released);
    const transmittalId = Number(r.lastInsertRowid);
    let pad = input.starting_pad;
    let siStart = input.starting_si;
    for (let i = 0; i < input.total_pads; i++) {
      const siEnd = siStart + siPerPad - 1;
      detail.run(transmittalId, pad, siStart, siEnd);
      pad++;
      siStart = siEnd + 1;
    }
    return transmittalId;
  });

  const id = run();
  db.close();
  return id;
}

export async function dbSearchTransmittals(q: string): Promise<TransmittalRow[]> {
  await ensureSchema();
  const like = `%${q}%`;

  if (getDbMode() === 'postgres') {
    const sql = await getPostgresSql();
    const result = await sql`
      SELECT id, from_branch, to_branch, released_by, date_released::text, created_at::text
      FROM tbl_transmittal
      WHERE from_branch ILIKE ${like}
         OR to_branch ILIKE ${like}
         OR released_by ILIKE ${like}
         OR CAST(id AS TEXT) ILIKE ${like}
         OR date_released::text ILIKE ${like}
      ORDER BY id DESC
      LIMIT 100
    `;
    return result.rows as TransmittalRow[];
  }

  const db = await getSqlite();
  const rows = db
    .prepare(
      `SELECT id, from_branch, to_branch, released_by, date_released, created_at
       FROM tbl_transmittal
       WHERE from_branch LIKE ? COLLATE NOCASE
          OR to_branch LIKE ? COLLATE NOCASE
          OR released_by LIKE ? COLLATE NOCASE
          OR CAST(id AS TEXT) LIKE ?
          OR date_released LIKE ?
       ORDER BY id DESC
       LIMIT 100`
    )
    .all(like, like, like, like, like) as TransmittalRow[];
  db.close();
  return rows;
}

export async function dbGetTransmittalById(id: number): Promise<TransmittalRow | null> {
  await ensureSchema();

  if (getDbMode() === 'postgres') {
    const sql = await getPostgresSql();
    const result = await sql`
      SELECT id, from_branch, to_branch, released_by, date_released::text, created_at::text
      FROM tbl_transmittal WHERE id = ${id}
    `;
    return (result.rows[0] as TransmittalRow) ?? null;
  }

  const db = await getSqlite();
  const row = db
    .prepare(
      `SELECT id, from_branch, to_branch, released_by, date_released, created_at
       FROM tbl_transmittal WHERE id = ?`
    )
    .get(id) as TransmittalRow | undefined;
  db.close();
  return row ?? null;
}

export async function dbGetTransmittalDetails(id: number): Promise<DetailRow[]> {
  await ensureSchema();

  if (getDbMode() === 'postgres') {
    const sql = await getPostgresSql();
    const result = await sql`
      SELECT id, transmittal_id, pad_no, si_start, si_end
      FROM tbl_transmittal_details
      WHERE transmittal_id = ${id}
      ORDER BY pad_no ASC
    `;
    return result.rows as DetailRow[];
  }

  const db = await getSqlite();
  const rows = db
    .prepare(
      `SELECT id, transmittal_id, pad_no, si_start, si_end
       FROM tbl_transmittal_details
       WHERE transmittal_id = ?
       ORDER BY pad_no ASC`
    )
    .all(id) as DetailRow[];
  db.close();
  return rows;
}
