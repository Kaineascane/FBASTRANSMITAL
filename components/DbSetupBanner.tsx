export default function DbSetupBanner() {
  return (
    <div className="alert alert-error" role="alert">
      <strong>Database not configured for production.</strong>
      <p className="mb-0 mt-2">
        On <strong>Vercel</strong>, add <strong>Storage → Postgres</strong> to your project (sets <code>POSTGRES_URL</code>).
        For <strong>local dev</strong>, run <code>npm install</code> then <code>npm run dev</code> — tables are created automatically in{' '}
        <code>data/transmittal.db</code>.
      </p>
    </div>
  );
}
