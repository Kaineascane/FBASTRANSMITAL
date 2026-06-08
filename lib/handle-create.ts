import { ensureSchema } from '@/lib/db';
import { flashRedirectUrl } from '@/lib/flash';
import { getRequestBaseUrl } from '@/lib/request-url';
import { saveTransmittal, validateTransmittalInput } from '@/lib/transmittal';

export async function handleCreateTransmittal(
  formData: FormData,
  request?: Request
): Promise<Response> {
  const base = request ? getRequestBaseUrl(request) : 'http://localhost:3000/';

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
  const old = validation.data as Record<string, string | number>;

  if (!validation.valid) {
    return Response.redirect(new URL(flashRedirectUrl('/', validation.errors, old), base), 303);
  }

  try {
    await ensureSchema();
    const id = await saveTransmittal(validation.data);
    return Response.redirect(new URL(`/print/${id}`, base), 303);
  } catch {
    return Response.redirect(
      new URL(flashRedirectUrl('/', ['Could not save to database. Please try again.'], old), base),
      303
    );
  }
}
