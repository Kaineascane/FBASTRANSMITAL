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
