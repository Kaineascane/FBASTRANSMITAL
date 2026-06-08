'use server';

import { headers } from 'next/headers';
import { handleCreateTransmittal } from '@/lib/handle-create';

export async function createTransmittal(formData: FormData): Promise<void> {
  const h = await headers();
  const host = h.get('x-forwarded-host') ?? h.get('host') ?? 'localhost:3000';
  const proto = h.get('x-forwarded-proto') ?? 'http';
  const url = `${proto}://${host}/`;
  const request = new Request(url);
  const response = await handleCreateTransmittal(formData, request);
  // Server Actions: follow redirect manually
  const location = response.headers.get('Location');
  if (location) {
    const { redirect } = await import('next/navigation');
    redirect(location.replace(new URL(url).origin, '') || location);
  }
}
