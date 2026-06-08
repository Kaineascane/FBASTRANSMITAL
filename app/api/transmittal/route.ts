import { handleCreateTransmittal } from '@/lib/handle-create';

export async function POST(request: Request): Promise<Response> {
  const formData = await request.formData();
  return handleCreateTransmittal(formData, request);
}
