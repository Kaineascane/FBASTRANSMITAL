import Link from 'next/link';
import AppHeader from '@/components/AppHeader';
import { ensureSchema } from '@/lib/db';
import { searchTransmittals } from '@/lib/transmittal';

export const dynamic = 'force-dynamic';

type Props = { searchParams: Promise<{ q?: string }> };

export default async function SearchPage({ searchParams }: Props) {
  await ensureSchema().catch(() => null);
  const { q: rawQ } = await searchParams;
  const q = (rawQ ?? '').trim();
  const records = q ? await searchTransmittals(q).catch(() => []) : [];
  const count = records.length;

  return (
    <>
      <AppHeader title="SEARCH / REPRINT" actionHref="/" actionLabel="New Transmittal" />
      <main className="app-main container">
        <div className="card-panel">
          <form method="GET" role="search">
            <label className="form-label" htmlFor="q">
              Find transmittal
            </label>
            <div className="row g-2 align-items-stretch">
              <div className="col-12 col-md-9">
                <div className="search-input-wrap">
                  <i className="bi bi-search search-icon" aria-hidden="true" />
                  <input
                    type="search"
                    name="q"
                    id="q"
                    className="form-control"
                    defaultValue={q}
                    placeholder="Branch, name, date, or ref #"
                    autoComplete="off"
                  />
                </div>
              </div>
              <div className="col-12 col-md-3">
                <button type="submit" className="btn btn-primary w-100 h-100">
                  <i className="bi bi-search" /> Search
                </button>
              </div>
            </div>
          </form>
        </div>

        {q === '' && (
          <p className="page-intro">
            Search past transmittals by branch, person, date, or reference number, then reprint.
          </p>
        )}

        {q !== '' && count === 0 && (
          <div className="card-panel empty-state">
            <i className="bi bi-inbox" />
            <p className="mb-0">No records found for &ldquo;{q}&rdquo;.</p>
          </div>
        )}

        {q !== '' && count > 0 && (
          <>
            <p className="page-intro mb-2">
              {count} result{count === 1 ? '' : 's'} found
            </p>

            <div className="records-mobile">
              {records.map((r) => (
                <article className="record-card" key={r.id}>
                  <div className="record-card-header">
                    <span className="record-ref">#{r.id}</span>
                    <span className="text-muted small">{r.date_released}</span>
                  </div>
                  <dl>
                    <dt>From</dt>
                    <dd>{r.from_branch}</dd>
                    <dt>To</dt>
                    <dd>{r.to_branch}</dd>
                    <dt>Released by</dt>
                    <dd>{r.released_by}</dd>
                  </dl>
                  <Link href={`/print/${r.id}`} className="btn btn-primary btn-reprint" target="_blank" rel="noopener">
                    <i className="bi bi-printer" /> Reprint
                  </Link>
                </article>
              ))}
            </div>

            <div className="card-panel p-0 overflow-hidden records-desktop">
              <div className="table-responsive">
                <table className="table table-hover mb-0 table-records">
                  <thead>
                    <tr>
                      <th>Ref #</th>
                      <th>From</th>
                      <th>To</th>
                      <th>Released by</th>
                      <th>Date</th>
                      <th />
                    </tr>
                  </thead>
                  <tbody>
                    {records.map((r) => (
                      <tr key={r.id}>
                        <td>
                          <strong>{r.id}</strong>
                        </td>
                        <td>{r.from_branch}</td>
                        <td>{r.to_branch}</td>
                        <td>{r.released_by}</td>
                        <td>{r.date_released}</td>
                        <td>
                          <Link href={`/print/${r.id}`} className="btn btn-sm btn-primary" target="_blank" rel="noopener">
                            <i className="bi bi-printer" /> Reprint
                          </Link>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          </>
        )}
      </main>
      <footer className="app-footer">
        <div className="container">
          <span>FBAS Insurance Agency Co. — S.I Transmittal</span>
        </div>
      </footer>
    </>
  );
}
