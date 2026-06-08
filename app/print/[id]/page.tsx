import { notFound } from 'next/navigation';
import PrintAutoTrigger from '@/components/PrintAutoTrigger';
import PrintSlip from '@/components/PrintSlip';
import PrintToolbar from '@/components/PrintToolbar';
import { ensureSchema } from '@/lib/db';
import { getTransmittalById, getTransmittalDetails, paginateSlipRows } from '@/lib/transmittal';

export const dynamic = 'force-dynamic';

type Props = { params: Promise<{ id: string }> };

const COPIES = ["ADMIN'S COPY", "ADMIN ASSISTANT'S COPY"];

export default async function PrintPage({ params }: Props) {
  await ensureSchema().catch(() => null);
  const { id: idStr } = await params;
  const id = parseInt(idStr, 10);
  if (id < 1) notFound();

  const row = await getTransmittalById(id).catch(() => null);
  if (!row) notFound();

  const details = await getTransmittalDetails(id);
  const pages = paginateSlipRows(details);
  const totalPads = details.length;

  return (
    <>
      <PrintToolbar pageCount={pages.length} totalPads={totalPads} />

      {pages.map((pageData, pageIndex) => (
        <div
          key={pageData.page}
          className={`print-page${pageIndex < pages.length - 1 ? ' print-page-break' : ''}`}
        >
          {COPIES.map((copyLabel, copyIndex) => (
            <div key={copyLabel}>
              <PrintSlip
                row={row}
                tableRows={pageData.rows}
                copyLabel={copyLabel}
                pageNum={pageData.page}
                totalPages={pageData.total}
                startNo={pageData.startNo}
              />
              {copyIndex === 0 && <div className="print-cut-line" aria-hidden="true" />}
            </div>
          ))}
        </div>
      ))}

      <PrintAutoTrigger id={id} />
    </>
  );
}
