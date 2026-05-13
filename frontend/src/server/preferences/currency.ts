import 'server-only';
import { cookies } from 'next/headers';
import {
  CURRENCY_COOKIE_NAME,
  DEFAULT_CURRENCY,
  isCurrency,
  type Currency,
} from '@/types/currency';

export async function getActiveCurrency(): Promise<Currency> {
  const store = await cookies();
  const value = store.get(CURRENCY_COOKIE_NAME)?.value;
  return isCurrency(value) ? value : DEFAULT_CURRENCY;
}
