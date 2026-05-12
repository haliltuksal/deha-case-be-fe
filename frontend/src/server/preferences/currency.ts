import 'server-only';
import { cookies } from 'next/headers';
import {
  CURRENCY_COOKIE_NAME,
  DEFAULT_CURRENCY,
  isCurrency,
  type Currency,
} from '@/types/currency';

/**
 * Resolves the active display currency for the current request. The cookie
 * is set by `setCurrencyPreference` (server action) when the user changes
 * the currency in the header switcher; otherwise the default (`TRY`) is
 * used. The cookie is intentionally *not* HttpOnly — it is a UI preference,
 * not an authentication artefact.
 */
export async function getActiveCurrency(): Promise<Currency> {
  const store = await cookies();
  const value = store.get(CURRENCY_COOKIE_NAME)?.value;
  return isCurrency(value) ? value : DEFAULT_CURRENCY;
}
