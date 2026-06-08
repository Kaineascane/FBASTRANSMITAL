import Link from 'next/link';

type Props = {
  title?: string;
  actionHref?: string;
  actionLabel?: string;
};

export default function AppHeader({
  title = 'S.I TRANSMITTAL SYSTEM',
  actionHref = '/search',
  actionLabel = 'Search / Reprint',
}: Props) {
  return (
    <header className="app-header">
      <div className="container app-header-inner">
        <Link href="/" className="app-brand" aria-label="Home">
          <span className="app-brand-icon">
            <i className="bi bi-receipt" />
          </span>
          <span className="app-brand-text">{title}</span>
        </Link>
        <nav className="app-nav">
          <Link href={actionHref} className="btn btn-header-action">
            <i className="bi bi-search" />
            <span>{actionLabel}</span>
          </Link>
        </nav>
      </div>
    </header>
  );
}
