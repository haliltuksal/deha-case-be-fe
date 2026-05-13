import 'server-only';
import { env } from '@/config/env';
import type { ApiErrorBody } from '@/types/api';
import { HttpError } from './http-error';
import { getRequestContext } from './request-context';
import { REQUEST_ID_HEADER } from './request-id';

interface LaravelRequestInit extends Omit<RequestInit, 'body'> {
  token?: string;
  requestId?: string;
  json?: unknown;
  body?: BodyInit | null;
  timeoutMs?: number;
}

export async function laravel<T>(path: string, init: LaravelRequestInit = {}): Promise<T> {
  const {
    token,
    requestId: explicitRequestId,
    json,
    body,
    timeoutMs,
    headers,
    signal,
    ...rest
  } = init;

  const requestId = explicitRequestId ?? getRequestContext()?.requestId;

  const url = buildUrl(path);
  const finalHeaders = buildHeaders({ headers, token, requestId, hasJsonBody: json !== undefined });
  const finalBody = json !== undefined ? JSON.stringify(json) : (body ?? undefined);
  const finalSignal = signal ?? AbortSignal.timeout(timeoutMs ?? env.REQUEST_TIMEOUT_MS);

  let response: Response;
  try {
    response = await fetch(url, {
      ...rest,
      headers: finalHeaders,
      body: finalBody,
      signal: finalSignal,
    });
  } catch (cause) {
    throw HttpError.fromTransport(toTransportMessage(cause), requestId);
  }

  const responseRequestId = response.headers.get(REQUEST_ID_HEADER) ?? requestId;

  if (!response.ok) {
    const errorBody = await readErrorBody(response);
    throw new HttpError(response.status, errorBody, responseRequestId ?? undefined);
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
  const base = env.LARAVEL_API_URL.replace(/\/+$/, '');
  const suffix = path.startsWith('/') ? path : `/${path}`;
  return `${base}${suffix}`;
}

function buildHeaders(input: {
  headers: HeadersInit | undefined;
  token: string | undefined;
  requestId: string | undefined;
  hasJsonBody: boolean;
}): Headers {
  const headers = new Headers(input.headers);
  if (!headers.has('Accept')) {
    headers.set('Accept', 'application/json');
  }
  if (input.hasJsonBody && !headers.has('Content-Type')) {
    headers.set('Content-Type', 'application/json');
  }
  if (input.token) {
    headers.set('Authorization', `Bearer ${input.token}`);
  }
  if (input.requestId) {
    headers.set(REQUEST_ID_HEADER, input.requestId);
  }
  return headers;
}

async function readErrorBody(response: Response): Promise<ApiErrorBody> {
  try {
    const parsed = (await response.json()) as Partial<ApiErrorBody> | null;
    if (parsed && typeof parsed.message === 'string' && typeof parsed.code === 'string') {
      return parsed as ApiErrorBody;
    }
  } catch {
    /* fall through */
  }
  return {
    status: 'error',
    message: `Upstream returned HTTP ${response.status}.`,
    data: null,
    code: response.status === 401 ? 'ERR_UNAUTHENTICATED' : 'ERR_HTTP',
  };
}

function toTransportMessage(cause: unknown): string {
  if (cause instanceof DOMException && cause.name === 'AbortError') {
    return 'Upstream request timed out.';
  }
  if (cause instanceof Error && cause.message) {
    return cause.message;
  }
  return 'Upstream request failed.';
}
