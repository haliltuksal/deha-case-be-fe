import 'server-only';
import { randomUUID } from 'node:crypto';

export const REQUEST_ID_HEADER = 'X-Request-Id';

export function getOrCreateRequestId(headers: Headers): string {
  const existing = headers.get(REQUEST_ID_HEADER);
  return existing && existing.length > 0 ? existing : randomUUID();
}
