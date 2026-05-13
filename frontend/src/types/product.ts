import type { Currency } from './currency';

export type CurrencyAmounts = Record<Currency, string>;

export interface Product {
  id: number;
  name: string;
  description: string;
  price: string;
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
