import Image from 'next/image';
import { formatTransmittalDate } from '@/lib/format';
import type { DetailRow, TransmittalRow } from '@/lib/transmittal';

type Props = {
  row: TransmittalRow;
  tableRows: (DetailRow | null)[];
  copyLabel: string;
  pageNum: number;
  totalPages: number;
  startNo: number;
};

export default function PrintSlip({ row, tableRows, copyLabel, pageNum, totalPages, startNo }: Props) {
  const dateReleased = formatTransmittalDate(row.date_released);

  return (
    <article className="fbas-slip">
      <div className="fbas-doc-ref">ORTS.REV003-05032024</div>

      <header className="fbas-header">
        <div className="fbas-logo" aria-hidden="true">
          <Image
            src="/assets/img/logo.png"
            alt="FBAS Insurance Agency Co."
            className="fbas-logo-img"
            width={120}
            height={48}
            priority
          />
        </div>
        <div className="fbas-company">
          <div className="fbas-company-name">FBAS INSURANCE AGENCY CO.</div>
          <div className="fbas-company-addr">126 Kumintang Ilaya, Batangas City, Capital, Batangas</div>
          <div className="fbas-slip-title">S.I. TRANSMITTAL SLIP</div>
        </div>
      </header>

      <div className="fbas-meta">
        <div className="fbas-field">
          <span className="fbas-field-label">FROM:</span>
          <span className="fbas-field-value">{row.from_branch}</span>
        </div>
        <div className="fbas-field">
          <span className="fbas-field-label">TO:</span>
          <span className="fbas-field-value">{row.to_branch}</span>
        </div>
        <div className="fbas-field">
          <span className="fbas-field-label">DATE RELEASED:</span>
          <span className="fbas-field-value">{dateReleased}</span>
        </div>
        <div className="fbas-field fbas-field-signature">
          <span className="fbas-field-label">RELEASED BY:</span>
          <span className="fbas-field-value">{row.released_by}</span>
          <div className="fbas-sig-caption">Signature Over Printed Name</div>
        </div>
      </div>

      <div className="fbas-table-block">
        <table className="fbas-table fbas-table-20">
          <thead>
            <tr>
              <th className="col-no">NO.</th>
              <th className="col-pad">PAD NO.</th>
              <th className="col-si">S.I. SERIES NO.</th>
            </tr>
          </thead>
          <tbody>
            {tableRows.map((detail, i) => (
              <tr key={i}>
                <td>{startNo + i}</td>
                <td>{detail ? detail.pad_no : '\u00A0'}</td>
                <td>
                  {detail ? `${detail.si_start}-${detail.si_end}` : '\u00A0'}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
        <aside className="fbas-side-boxes">
          <div className="fbas-side-page">
            PAGE <span className="fbas-underline">{pageNum}</span> OF <span className="fbas-underline">{totalPages}</span>
          </div>
          <div className="fbas-side-copy">{copyLabel}</div>
        </aside>
      </div>

      <footer className="fbas-footer">
        <div className="fbas-footer-row">
          <div className="fbas-footer-sig">
            <div className="fbas-footer-label">DELIVERED BY:</div>
            <div className="fbas-footer-sign-area" />
            <div className="fbas-sig-caption">Signature Over Printed Name</div>
          </div>
          <div className="fbas-footer-date">
            <span className="fbas-footer-date-label">DATE:</span>
            <div className="fbas-footer-date-line" />
          </div>
        </div>
        <div className="fbas-footer-row">
          <div className="fbas-footer-sig">
            <div className="fbas-footer-label">RECEIVED BY:</div>
            <div className="fbas-footer-sign-area" />
            <div className="fbas-sig-caption">Signature Over Printed Name</div>
          </div>
          <div className="fbas-footer-date">
            <span className="fbas-footer-date-label">DATE:</span>
            <div className="fbas-footer-date-line" />
          </div>
        </div>
      </footer>
    </article>
  );
}
