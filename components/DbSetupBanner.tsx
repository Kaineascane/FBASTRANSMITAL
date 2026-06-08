export default function DbSetupBanner() {
  return (
    <div className="alert alert-error" role="alert">
      <strong>Database not configured.</strong>
      <p className="mb-0 mt-2">
        Add <code>POSTGRES_URL</code> to <code>.env.local</code> (from Vercel Storage or{' '}
        <a href="https://neon.tech" target="_blank" rel="noopener noreferrer">Neon</a>), then visit{' '}
        <code>/api/setup?key=YOUR_SETUP_SECRET</code> once.
      </p>
    </div>
  );
}
