import AppHeader from '@/components/AppHeader';
import DbSetupBanner from '@/components/DbSetupBanner';
import TransmittalForm from '@/components/TransmittalForm';
import { ensureSchema } from '@/lib/db';
import { decodeFlashParam } from '@/lib/flash';
import { getNextSeries } from '@/lib/transmittal';

export const dynamic = 'force-dynamic';

type Props = { searchParams: Promise<{ flash?: string }> };

export default async function HomePage({ searchParams }: Props) {
  const dbConfigured = Boolean(process.env.POSTGRES_URL);

  if (dbConfigured) {
    await ensureSchema().catch(() => null);
  }

  const { flash } = await searchParams;
  const { errors, old } = decodeFlashParam(flash);

  const next = dbConfigured
    ? await getNextSeries().catch(() => ({ pad: 1, si: 1 }))
    : { pad: 1, si: 1 };
  const today = new Date().toISOString().slice(0, 10);
  const lastSiEnd = Math.max(0, next.si - 1);

  const defaults = {
    from_branch: String(old.from_branch ?? ''),
    to_branch: String(old.to_branch ?? ''),
    released_by: String(old.released_by ?? ''),
    date_released: String(old.date_released ?? today),
    starting_pad: Number(old.starting_pad ?? next.pad),
    starting_si: Number(old.starting_si ?? next.si),
    total_pads: old.total_pads !== undefined ? Number(old.total_pads) : '',
  };

  return (
    <>
      <AppHeader />
      <main className="app-main container">
        {!dbConfigured && <DbSetupBanner />}
        <TransmittalForm
          errors={errors}
          nextPad={next.pad}
          nextSi={next.si}
          lastSiEnd={lastSiEnd}
          today={today}
          defaults={defaults}
        />
      </main>
      <footer className="app-footer">
        <div className="container">
          <span>FBAS Insurance Agency Co. — S.I Transmittal</span>
        </div>
      </footer>
    </>
  );
}
