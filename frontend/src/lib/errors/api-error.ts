import type { ApiErrorBody, ApiErrorCode } from '@/types/api';

/**
 * Browser-side mirror of the backend error envelope. Thrown by `apiClient`
 * whenever the BFF returns a non-2xx response and consumed by UI code to
 * branch on stable error codes (e.g. ERR_INSUFFICIENT_STOCK).
 */
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
