import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const dataDir = path.join(__dirname, '..', 'data');
const dbPath = path.join(dataDir, 'transmittal.db');

if (!fs.existsSync(dataDir)) fs.mkdirSync(dataDir, { recursive: true });

const Database = (await import('better-sqlite3')).default;
const db = new Database(dbPath);

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
console.log('OK: Tables ready at', dbPath);
