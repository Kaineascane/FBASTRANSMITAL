import { NextRequest, NextResponse } from 'next/server';
import { ensureSchema } from '@/lib/db';

export async function GET(request: NextRequest): Promise<NextResponse> {
  const key = request.nextUrl.searchParams.get('key');
  const secret = process.env.SETUP_SECRET;

  if (!secret || key !== secret) {
    return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  }

  try {
    await ensureSchema();
    return NextResponse.json({ ok: true, message: 'Database tables created.' });
  } catch (e) {
    const message = e instanceof Error ? e.message : 'Setup failed';
    return NextResponse.json({ error: message }, { status: 500 });
  }
}
