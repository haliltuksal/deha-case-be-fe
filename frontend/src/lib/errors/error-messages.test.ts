import { describe, expect, it } from 'vitest';
import { getErrorMessage, __ERROR_MESSAGES_FOR_TEST__ } from './error-messages';

const KNOWN_CODES = [
  'ERR_VALIDATION',
  'ERR_INVALID_CREDENTIALS',
  'ERR_UNAUTHENTICATED',
  'ERR_UNAUTHORIZED',
  'ERR_NOT_FOUND',
  'ERR_METHOD_NOT_ALLOWED',
  'ERR_TOO_MANY_REQUESTS',
  'ERR_INSUFFICIENT_STOCK',
  'ERR_EMPTY_CART',
  'ERR_INVALID_ORDER_TRANSITION',
  'ERR_TOKEN_EXPIRED',
  'ERR_TOKEN_BLACKLISTED',
  'ERR_TOKEN_INVALID',
  'ERR_TOKEN_ABSENT',
  'ERR_EXCHANGE_PROVIDER_FAILED',
  'ERR_EXCHANGE_RATE_UNAVAILABLE',
  'ERR_HTTP',
  'ERR_INTERNAL',
] as const;

describe('getErrorMessage', () => {
  it('returns a Turkish message for every known error code', () => {
    for (const code of KNOWN_CODES) {
      const message = getErrorMessage(code);
      expect(message).toBeTypeOf('string');
      expect(message.length).toBeGreaterThan(0);
    }
  });

  it('keeps the message map in sync with the known codes', () => {
    expect(Object.keys(__ERROR_MESSAGES_FOR_TEST__).sort()).toEqual([...KNOWN_CODES].sort());
  });

  it('returns the provided fallback for an unknown code', () => {
    expect(getErrorMessage('ERR_DOES_NOT_EXIST', 'custom')).toBe('custom');
  });

  it('returns the generic message when no fallback is provided for an unknown code', () => {
    expect(getErrorMessage('ERR_DOES_NOT_EXIST')).toMatch(/beklenmeyen/i);
  });

  it('returns the generic message when code is undefined', () => {
    expect(getErrorMessage(undefined)).toMatch(/beklenmeyen/i);
  });
});
