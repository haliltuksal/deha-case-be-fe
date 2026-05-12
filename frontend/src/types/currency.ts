export const SUPPORTED_CURRENCIES = ['TRY', 'USD', 'EUR'] as const;

export type Currency = (typeof SUPPORTED_CURRENCIES)[number];

export const DEFAULT_CURRENCY: Currency = 'TRY';

export const CURRENCY_COOKIE_NAME = 'deha_currency';

export function isCurrency(value: unknown): value is Currency {
  return typeof value === 'string' && (SUPPORTED_CURRENCIES as readonly string[]).includes(value);
}
