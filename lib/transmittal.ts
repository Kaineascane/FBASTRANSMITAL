import { sql } from './db';
import { SI_PER_PAD, SLIP_ROWS_PER_PAGE } from './constants';

export type TransmittalRow = {
  id: number;
  from_branch: string;
  to_branch: string;
  released_by: string;
  date_released: string;
  created_at: string;
};

export type DetailRow = {
  id: number;
  transmittal_id: number;
  pad_no: number;
  si_start: number;
  si_end: number;
};

export type TransmittalInput = {
  from_branch: string;
  to_branch: string;
  released_by: string;
  date_released: string;
  starting_pad: number;
  starting_si: number;
  total_pads: number;
};

export type SlipPage = {
  rows: (DetailRow | null)[];
  page: number;
  total: number;
  startNo: number;
};

export async function getNextSeries(): Promise<{ pad: number; si: number }> {
  let pad = 1;
  let si = 1;

  const padResult = await sql`SELECT MAX(pad_no) AS last_pad FROM tbl_transmittal_details`;
  const lastPad = padResult.rows[0]?.last_pad;
  if (lastPad != null) pad = Number(lastPad) + 1;

  const siResult = await sql`SELECT MAX(si_end) AS last_si FROM tbl_transmittal_details`;
  const lastSi = siResult.rows[0]?.last_si;
  if (lastSi != null) si = Number(lastSi) + 1;

  return { pad, si };
}

export function validateTransmittalInput(data: TransmittalInput): {
  valid: boolean;
  errors: string[];
  data: TransmittalInput;
} {
  const errors: string[] = [];
  const from = data.from_branch.trim();
  const to = data.to_branch.trim();
  const released = data.released_by.trim();
  const date = data.date_released.trim();

  if (!from) errors.push('FROM branch is required.');
  if (!to) errors.push('TO branch is required.');
  if (!released) errors.push('RELEASED BY is required.');
  if (!date) {
    errors.push('DATE RELEASED is required.');
  } else if (!/^\d{4}-\d{2}-\d{2}$/.test(date) || isNaN(Date.parse(date))) {
    errors.push('DATE RELEASED must be a valid date.');
  }
  if (data.starting_pad < 1) errors.push('STARTING PAD NO must be at least 1.');
  if (data.starting_si < 1) errors.push('STARTING S.I NO must be at least 1.');
  if (data.total_pads < 1) errors.push('TOTAL PADS is required (minimum 1).');
  else if (data.total_pads > 500) errors.push('TOTAL PADS cannot exceed 500.');

  return {
    valid: errors.length === 0,
    errors,
    data: {
      from_branch: from,
      to_branch: to,
      released_by: released,
      date_released: date,
      starting_pad: data.starting_pad,
      starting_si: data.starting_si,
      total_pads: data.total_pads,
    },
  };
}

export async function saveTransmittal(input: TransmittalInput): Promise<number> {
  const header = await sql`
    INSERT INTO tbl_transmittal (from_branch, to_branch, released_by, date_released)
    VALUES (${input.from_branch}, ${input.to_branch}, ${input.released_by}, ${input.date_released})
    RETURNING id
  `;
  const transmittalId = Number(header.rows[0].id);

  let pad = input.starting_pad;
  let siStart = input.starting_si;

  for (let i = 0; i < input.total_pads; i++) {
    const siEnd = siStart + SI_PER_PAD - 1;
    await sql`
      INSERT INTO tbl_transmittal_details (transmittal_id, pad_no, si_start, si_end)
      VALUES (${transmittalId}, ${pad}, ${siStart}, ${siEnd})
    `;
    pad++;
    siStart = siEnd + 1;
  }

  return transmittalId;
}

export async function searchTransmittals(q: string): Promise<TransmittalRow[]> {
  const like = `%${q}%`;
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

export async function getTransmittalById(id: number): Promise<TransmittalRow | null> {
  const result = await sql`
    SELECT id, from_branch, to_branch, released_by, date_released::text, created_at::text
    FROM tbl_transmittal WHERE id = ${id}
  `;
  return (result.rows[0] as TransmittalRow) ?? null;
}

export async function getTransmittalDetails(id: number): Promise<DetailRow[]> {
  const result = await sql`
    SELECT id, transmittal_id, pad_no, si_start, si_end
    FROM tbl_transmittal_details
    WHERE transmittal_id = ${id}
    ORDER BY pad_no ASC
  `;
  return result.rows as DetailRow[];
}

export function paginateSlipRows(allRows: DetailRow[], perPage = SLIP_ROWS_PER_PAGE): SlipPage[] {
  const count = allRows.length;
  const totalPages = Math.max(1, Math.ceil(count / perPage));
  const pages: SlipPage[] = [];

  for (let p = 0; p < totalPages; p++) {
    const chunk: (DetailRow | null)[] = allRows.slice(p * perPage, p * perPage + perPage);
    while (chunk.length < perPage) chunk.push(null);
    pages.push({
      rows: chunk,
      page: p + 1,
      total: totalPages,
      startNo: p * perPage + 1,
    });
  }

  return pages;
}
