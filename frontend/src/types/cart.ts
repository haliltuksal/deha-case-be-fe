import type { CurrencyAmounts } from './product';

export interface CartItem {
  id: number;
  product_id: number;
  name: string;
  quantity: number;
  unit_price: string;
  line_total: string;
  unit_prices: CurrencyAmounts;
  line_totals: CurrencyAmounts;
  available_stock: number;
}

export interface Cart {
  id: number;
  items: ReadonlyArray<CartItem>;
  subtotal: string;
  totals: CurrencyAmounts;
  item_count: number;
  updated_at: string;
}
