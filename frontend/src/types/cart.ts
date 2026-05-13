import type { Currency } from './currency';
import type { CurrencyAmounts } from './product';

export interface CartItem {
  product_id: number;
  name: string;
  quantity: number;
  stock_available: number;
  unit_price: string;
  unit_currency: Currency;
  subtotal: CurrencyAmounts;
}

export interface Cart {
  id: number;
  items: ReadonlyArray<CartItem>;
  totals: CurrencyAmounts;
  item_count: number;
  total_quantity: number;
}
