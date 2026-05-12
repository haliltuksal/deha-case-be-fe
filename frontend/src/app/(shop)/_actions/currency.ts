'use server';

import { cookies } from 'next/headers';
import { CURRENCY_COOKIE_NAME, isCurrency, type Currency } from '@/types/currency';

const ONE_YEAR_SECONDS = 60 * 60 * 24 * 365;

/**
 * Server action invoked by the header CurrencySwitcher. Persists the chosen
 * currency in a same-site cookie so the preference survives page reloads
 * and tab navigations without leaking into the JWT cookie's surface area.
 */
export async function setCurrencyPreference(currency: Currency): Promise<void> {
  if (!isCurrency(currency)) {
    throw new Error('Unsupported currency.');
  }

  const store = await cookies();
  store.set({
    name: CURRENCY_COOKIE_NAME,
    value: currency,
    maxAge: ONE_YEAR_SECONDS,
    sameSite: 'lax',
    path: '/',
    secure: process.env.NODE_ENV === 'production',
  });
}
