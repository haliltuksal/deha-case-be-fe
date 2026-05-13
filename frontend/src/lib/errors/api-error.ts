import type { ApiErrorBody, ApiErrorCode } from '@/types/api';

export class ApiError extends Error {
  public readonly status: number;
  public readonly code: ApiErrorCode;
  public readonly details: Readonly<Record<string, unknown>> | undefined;
  public readonly fieldErrors: Readonly<Record<string, ReadonlyArray<string>>> | undefined;

  constructor(status: number, body: ApiErrorBody) {
    super(body.message);
    this.name = 'ApiError';
    this.status = status;
    this.code = body.code;
    this.details = body.details;
    this.fieldErrors = body.errors;
  }
}

export function isApiError(value: unknown): value is ApiError {
  return value instanceof ApiError;
}
