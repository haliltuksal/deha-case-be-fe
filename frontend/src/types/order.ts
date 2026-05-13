import type { Currency } from './currency';
import type { CurrencyAmounts } from './product';

export const ORDER_STATUSES = ['pending', 'completed', 'cancelled'] as const;
export type OrderStatus = (typeof ORDER_STATUSES)[number];

export interface OrderItem {
  product_id: number | null;
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
  total_amount: string;
  currency: Currency;
  totals: CurrencyAmounts;
  items: ReadonlyArray<OrderItem>;
  created_at: string;
  updated_at: string;
}
