import { cookies } from 'next/headers';

export type FlashData = {
  errors: string[];
  old: Record<string, string | number>;
};

export async function setFlash(errors: string[], old: Record<string, string | number>): Promise<void> {
  const store = await cookies();
  store.set('flash_errors', JSON.stringify(errors), { maxAge: 120, httpOnly: true, path: '/' });
  store.set('flash_old', JSON.stringify(old), { maxAge: 120, httpOnly: true, path: '/' });
}

export async function consumeFlash(): Promise<FlashData> {
  const store = await cookies();
  const errorsRaw = store.get('flash_errors')?.value;
  const oldRaw = store.get('flash_old')?.value;

  if (errorsRaw) store.delete('flash_errors');
  if (oldRaw) store.delete('flash_old');

  let errors: string[] = [];
  let old: Record<string, string | number> = {};

  try {
    if (errorsRaw) errors = JSON.parse(errorsRaw);
    if (oldRaw) old = JSON.parse(oldRaw);
  } catch {
    /* ignore */
  }

  return { errors, old };
}
