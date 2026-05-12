import type { Currency } from './currency';

/**
 * Multi-currency price view that the backend returns alongside the canonical
 * value. Each entry is a decimal string, matching the precision used on the
 * server.
 */
export type CurrencyAmounts = Record<Currency, string>;

export interface Product {
  id: number;
  name: string;
  description: string;
  /** Price expressed in `base_currency`, decimal string. */
  price: string;
  /** Currency the price is denominated in (TRY by default). */
  base_currency: Currency;
  stock: number;
  prices: CurrencyAmounts;
  created_at: string;
  updated_at: string;
}

export interface ProductInput {
  name: string;
  description: string;
  price: string;
  base_currency: Currency;
  stock: number;
}
