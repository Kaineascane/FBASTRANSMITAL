'use client';

import Link from 'next/link';

type Props = {
  pageCount: number;
  totalPads: number;
};

export default function PrintToolbar({ pageCount, totalPads }: Props) {
  return (
    <nav className="no-print print-toolbar">
      <button type="button" onClick={() => window.print()} className="print-btn">
        Print
      </button>
      <Link href="/">New Transmittal</Link>
      <Link href="/search">Search / Reprint</Link>
      <span className="print-hint">
        Landscape · {pageCount} sheet{pageCount === 1 ? '' : 's'} · {totalPads} pad
        {totalPads === 1 ? '' : 's'} · 20 NO. per page
      </span>
    </nav>
  );
}
