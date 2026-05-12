import 'server-only';
import { NextResponse } from 'next/server';
import type { ApiErrorBody } from '@/types/api';
import { HttpError, isHttpError } from '@/server/http/http-error';
import { getRequestContext } from '@/server/http/request-context';
import { REQUEST_ID_HEADER } from '@/server/http/request-id';

/**
 * Translates any error caught inside a BFF route handler into a NextResponse
 * with the canonical error envelope. The frontend therefore sees the same
 * shape regardless of whether the failure originated upstream (Laravel) or
 * inside the proxy layer.
 */
export function toNextResponse(error: unknown): NextResponse<ApiErrorBody> {
  if (isHttpError(error)) {
    return buildResponse(error.status === 0 ? 502 : error.status, error.body, error.requestId);
  }

  if (error instanceof Error) {
    console.error('[bff] unhandled error', error);
    return buildResponse(500, {
      status: 'error',
      message: 'An unexpected error occurred.',
      data: null,
      code: 'ERR_INTERNAL',
    });
  }

  console.error('[bff] unhandled non-error throw', error);
  return buildResponse(500, {
    status: 'error',
    message: 'An unexpected error occurred.',
    data: null,
    code: 'ERR_INTERNAL',
  });
}

function buildResponse(
  status: number,
  body: ApiErrorBody,
  requestId?: string,
): NextResponse<ApiErrorBody> {
  const headers: Record<string, string> = {};
  const finalRequestId = requestId ?? getRequestContext()?.requestId;
  if (finalRequestId) {
    headers[REQUEST_ID_HEADER] = finalRequestId;
  }
  return NextResponse.json(body, { status, headers });
}

export { HttpError };
