import 'server-only';
import { NextResponse } from 'next/server';
import { getRequestContext } from '@/server/http/request-context';
import { REQUEST_ID_HEADER } from '@/server/http/request-id';

export function bffSuccess<T>(
  data: T,
  init: { status?: number; message?: string | null } = {},
): NextResponse {
  const headers: Record<string, string> = {};
  const requestId = getRequestContext()?.requestId;
  if (requestId) {
    headers[REQUEST_ID_HEADER] = requestId;
  }
  return NextResponse.json(
    {
      status: 'success',
      message: init.message ?? null,
      data,
    },
    { status: init.status ?? 200, headers },
  );
}
