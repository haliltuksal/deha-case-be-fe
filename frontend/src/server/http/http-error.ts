import 'server-only';
import type { ApiErrorBody, ApiErrorCode } from '@/types/api';

export class HttpError extends Error {
  public readonly status: number;
  public readonly code: ApiErrorCode;
  public readonly body: ApiErrorBody;
  public readonly requestId: string | undefined;

  constructor(status: number, body: ApiErrorBody, requestId?: string) {
    super(body.message);
    this.name = 'HttpError';
    this.status = status;
    this.code = body.code;
    this.body = body;
    this.requestId = requestId;
  }

  static fromTransport(message: string, requestId?: string): HttpError {
    return new HttpError(0, { status: 'error', message, data: null, code: 'ERR_HTTP' }, requestId);
  }

  static unauthenticated(): HttpError {
    return new HttpError(401, {
      status: 'error',
      message: 'Authentication required.',
      data: null,
      code: 'ERR_UNAUTHENTICATED',
    });
  }

  static forbidden(): HttpError {
    return new HttpError(403, {
      status: 'error',
      message: 'You are not allowed to perform this action.',
      data: null,
      code: 'ERR_UNAUTHORIZED',
    });
  }
}

export function isHttpError(value: unknown): value is HttpError {
  return value instanceof HttpError;
}
