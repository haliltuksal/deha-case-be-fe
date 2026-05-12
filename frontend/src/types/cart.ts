import type { Currency } from './currency';
import type { CurrencyAmounts } from './product';

export interface CartItem {
  product_id: number;
  name: string;
  quantity: number;
  stock_available: number;
  /** Stored as a decimal string in `unit_currency`. */
  unit_price: string;
  unit_currency: Currency;
  /** Per-line total rendered across every supported currency. */
  subtotal: CurrencyAmounts;
}

export interface Cart {
  id: number;
  items: ReadonlyArray<CartItem>;
  totals: CurrencyAmounts;
  /** Number of distinct line items in the cart. */
  item_count: number;
  /** Sum of `item.quantity` across the cart. */
  total_quantity: number;
}
