import type { Currency } from './currency';
import type { CurrencyAmounts } from './product';

export const ORDER_STATUSES = ['pending', 'completed', 'cancelled'] as const;
export type OrderStatus = (typeof ORDER_STATUSES)[number];

export interface OrderItem {
  /** May be null if the product has since been deleted (FK SET NULL). */
  product_id: number | null;
  /** Snapshot of the product name at the time of checkout. */
  product_name: string;
  unit_price: string;
  base_currency: Currency;
  quantity: number;
  line_total: string;
  line_total_display: CurrencyAmounts;
}

export interface Order {
  id: number;
  status: OrderStatus;
  /** Canonical TRY total persisted on the order header. */
  total_amount: string;
  currency: Currency;
  totals: CurrencyAmounts;
  items: ReadonlyArray<OrderItem>;
  created_at: string;
  updated_at: string;
}
