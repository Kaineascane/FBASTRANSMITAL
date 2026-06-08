'use server';

import { headers } from 'next/headers';
import { handleCreateTransmittal } from '@/lib/handle-create';
import { getRequestBaseUrl } from '@/lib/request-url';

export async function createTransmittal(formData: FormData): Promise<void> {
  const h = await headers();
  const request = new Request('http://localhost/', { headers: h });
  const url = getRequestBaseUrl(request);
  const response = await handleCreateTransmittal(formData, request);
  // Server Actions: follow redirect manually
  const location = response.headers.get('Location');
  if (location) {
    const { redirect } = await import('next/navigation');
    redirect(location.replace(new URL(url).origin, '') || location);
  }
}
