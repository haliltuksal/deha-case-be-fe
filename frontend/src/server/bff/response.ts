import 'server-only';
import { NextResponse } from 'next/server';
import { getRequestContext } from '@/server/http/request-context';
import { REQUEST_ID_HEADER } from '@/server/http/request-id';

/**
 * Build a BFF success response in the canonical envelope shape that the
 * Laravel backend uses: `{ status, message, data }`. Keeps the BFF and
 * upstream API consistent so client code reads the same shape regardless
 * of whether a request was server-rendered (page → repository → laravel)
 * or browser-initiated (browser → /api/* → repository → laravel).
 */
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
