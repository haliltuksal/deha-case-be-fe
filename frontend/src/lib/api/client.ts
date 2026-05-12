import 'client-only';
import type { ApiErrorBody } from '@/types/api';
import { ApiError } from '@/lib/errors/api-error';

interface ApiClientInit extends Omit<RequestInit, 'body'> {
  json?: unknown;
  body?: BodyInit | null;
}

/**
 * Browser-side wrapper around `fetch` that targets the Next.js BFF route
 * handlers under `/api/*`. The session cookie is included automatically
 * (same-origin) so callers never deal with the bearer token.
 *
 * Rejects with an `ApiError` on non-2xx responses so UI code can branch
 * on the canonical error codes coming from the backend.
 */
export async function apiClient<T>(path: string, init: ApiClientInit = {}): Promise<T> {
  const { json, body, headers, ...rest } = init;

  const finalHeaders = new Headers(headers);
  if (!finalHeaders.has('Accept')) {
    finalHeaders.set('Accept', 'application/json');
  }
  if (json !== undefined && !finalHeaders.has('Content-Type')) {
    finalHeaders.set('Content-Type', 'application/json');
  }

  const finalBody = json !== undefined ? JSON.stringify(json) : (body ?? undefined);

  const response = await fetch(buildUrl(path), {
    credentials: 'same-origin',
    ...rest,
    headers: finalHeaders,
    body: finalBody,
  });

  if (!response.ok) {
    const errorBody = await readErrorBody(response);
    throw new ApiError(response.status, errorBody);
  }

  if (response.status === 204) {
    return undefined as T;
  }

  return (await response.json()) as T;
}

function buildUrl(path: string): string {
  if (path.startsWith('http://') || path.startsWith('https://')) {
    return path;
  }
  return path.startsWith('/') ? path : `/${path}`;
}

async function readErrorBody(response: Response): Promise<ApiErrorBody> {
  try {
    const parsed = (await response.json()) as Partial<ApiErrorBody> | null;
    if (parsed && typeof parsed.message === 'string' && typeof parsed.code === 'string') {
      return parsed as ApiErrorBody;
    }
  } catch {
    // fall through
  }
  return {
    status: 'error',
    message: `Unexpected response (HTTP ${response.status}).`,
    data: null,
    code: 'ERR_HTTP',
  };
}
