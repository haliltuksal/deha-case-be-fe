import 'server-only';
import { HttpError } from '@/server/http/http-error';

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
