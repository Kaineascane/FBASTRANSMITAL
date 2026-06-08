'use server';

import { redirect } from 'next/navigation';
import { ensureSchema } from '@/lib/db';
import { setFlash } from '@/lib/flash';
import { saveTransmittal, validateTransmittalInput } from '@/lib/transmittal';

export async function createTransmittal(formData: FormData): Promise<void> {
  const input = {
    from_branch: String(formData.get('from_branch') ?? ''),
    to_branch: String(formData.get('to_branch') ?? ''),
    released_by: String(formData.get('released_by') ?? ''),
    date_released: String(formData.get('date_released') ?? ''),
    starting_pad: Number(formData.get('starting_pad') ?? 0),
    starting_si: Number(formData.get('starting_si') ?? 0),
    total_pads: Number(formData.get('total_pads') ?? 0),
  };

  const validation = validateTransmittalInput(input);
  if (!validation.valid) {
    await setFlash(validation.errors, validation.data as Record<string, string | number>);
    redirect('/');
  }

  let id: number;
  try {
    await ensureSchema();
    id = await saveTransmittal(validation.data);
  } catch {
    await setFlash(['Could not save to database. Please try again.'], validation.data as Record<string, string | number>);
    redirect('/');
  }

  redirect(`/print/${id}`);
}
