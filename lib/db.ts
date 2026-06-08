import { sql } from '@vercel/postgres';

export async function ensureSchema(): Promise<void> {
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
}

export { sql };
