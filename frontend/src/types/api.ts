export interface ApiSuccess<T> {
  status: 'success';
  message: string | null;
  data: T;
}

export interface ApiPaginated<T> {
  status: 'success';
  message: string | null;
  data: {
    items: ReadonlyArray<T>;
    pagination: Pagination;
  };
}

export interface Pagination {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface ApiErrorBody {
  status: 'error';
  message: string;
  data: null;
  code: ApiErrorCode;
  details?: Record<string, unknown>;
  errors?: Record<string, ReadonlyArray<string>>;
}

export type ApiErrorCode =
  | 'ERR_VALIDATION'
  | 'ERR_INVALID_CREDENTIALS'
  | 'ERR_UNAUTHENTICATED'
  | 'ERR_UNAUTHORIZED'
  | 'ERR_NOT_FOUND'
  | 'ERR_METHOD_NOT_ALLOWED'
  | 'ERR_TOO_MANY_REQUESTS'
  | 'ERR_INSUFFICIENT_STOCK'
  | 'ERR_EMPTY_CART'
  | 'ERR_INVALID_ORDER_TRANSITION'
  | 'ERR_TOKEN_EXPIRED'
  | 'ERR_TOKEN_BLACKLISTED'
  | 'ERR_TOKEN_INVALID'
  | 'ERR_TOKEN_ABSENT'
  | 'ERR_EXCHANGE_PROVIDER_FAILED'
  | 'ERR_EXCHANGE_RATE_UNAVAILABLE'
  | 'ERR_HTTP'
  | 'ERR_INTERNAL';
