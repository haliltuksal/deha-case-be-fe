import type { CurrencyAmounts } from './product';

export const ORDER_STATUSES = ['pending', 'completed', 'cancelled'] as const;
export type OrderStatus = (typeof ORDER_STATUSES)[number];

export interface OrderItem {
  id: number;
  product_id: number | null;
  name: string;
  quantity: number;
  unit_price: string;
  line_total: string;
  unit_prices: CurrencyAmounts;
  line_totals: CurrencyAmounts;
}

export interface Order {
  id: number;
  status: OrderStatus;
  subtotal: string;
  totals: CurrencyAmounts;
  item_count: number;
  items: ReadonlyArray<OrderItem>;
  created_at: string;
  updated_at: string;
}
