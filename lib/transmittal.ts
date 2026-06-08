import { SI_PER_PAD, SLIP_ROWS_PER_PAGE } from './constants';
import {
  dbGetNextSeries,
  dbGetTransmittalById,
  dbGetTransmittalDetails,
  dbSaveTransmittal,
  dbSearchTransmittals,
} from './db';
import type { DetailRow, SlipPage, TransmittalInput } from './transmittal-types';

export type { DetailRow, SlipPage, TransmittalInput, TransmittalRow } from './transmittal-types';

export async function getNextSeries(): Promise<{ pad: number; si: number }> {
  return dbGetNextSeries();
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
  return dbSaveTransmittal(input, SI_PER_PAD);
}

export async function searchTransmittals(q: string) {
  return dbSearchTransmittals(q);
}

export async function getTransmittalById(id: number) {
  return dbGetTransmittalById(id);
}

export async function getTransmittalDetails(id: number) {
  return dbGetTransmittalDetails(id);
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
