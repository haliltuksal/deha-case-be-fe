import type { Currency } from '@/types/currency';

const CURRENCY_LOCALES: Record<Currency, string> = {
  TRY: 'tr-TR',
  USD: 'en-US',
  EUR: 'de-DE',
};

const formatterCache = new Map<Currency, Intl.NumberFormat>();

function getFormatter(currency: Currency): Intl.NumberFormat {
  let formatter = formatterCache.get(currency);
  if (!formatter) {
    formatter = new Intl.NumberFormat(CURRENCY_LOCALES[currency], {
      style: 'currency',
      currency,
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });
    formatterCache.set(currency, formatter);
  }
  return formatter;
}

/**
 * Formats a monetary amount expressed as a decimal string in the given
 * currency. The backend stores prices as decimal strings to avoid IEEE-754
 * drift; we only convert to a number for display purposes here, and use
 * `Intl.NumberFormat` so each currency follows its native digit grouping.
 */
export function formatPrice(amount: string | number, currency: Currency): string {
  const numeric = typeof amount === 'number' ? amount : Number.parseFloat(amount);
  if (!Number.isFinite(numeric)) {
    throw new TypeError(`Cannot format non-numeric amount: ${amount}`);
  }
  return getFormatter(currency).format(numeric);
}
