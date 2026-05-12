import 'server-only';
import { HttpError } from '@/server/http/http-error';

/**
 * Resolves a numeric path parameter (e.g. `/api/products/[id]`) and throws a
 * canonical `ERR_NOT_FOUND` HttpError when the value is missing, malformed,
 * or non-positive. Centralises what every BFF detail/mutation route needs.
 */
export async function parseNumericParam(
  params: Promise<Record<string, string>>,
  key: string,
  notFoundMessage: string,
): Promise<number> {
  const resolved = await params;
  const raw = resolved[key];
  const parsed = raw ? Number.parseInt(raw, 10) : NaN;
  if (!Number.isInteger(parsed) || parsed <= 0) {
    throw new HttpError(404, {
      status: 'error',
      message: notFoundMessage,
      data: null,
      code: 'ERR_NOT_FOUND',
    });
  }
  return parsed;
}
