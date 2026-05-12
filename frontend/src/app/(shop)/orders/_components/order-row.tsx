import Link from 'next/link';
import { ChevronRight } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { formatPrice } from '@/lib/currency/format';
import { formatDateTime } from '@/lib/utils/format-date';
import type { Currency } from '@/types/currency';
import type { Order } from '@/types/order';
import { OrderStatusBadge } from './order-status-badge';

interface OrderRowProps {
  order: Order;
  currency: Currency;
}

export function OrderRow({ order, currency }: OrderRowProps) {
  const total = order.totals[currency] ?? order.total_amount;
  const itemCount = order.items.reduce((sum, item) => sum + item.quantity, 0);

  return (
    <Card className="flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between">
      <div className="space-y-1">
        <div className="flex flex-wrap items-center gap-3">
          <span className="text-base font-semibold tracking-tight">Sipariş #{order.id}</span>
          <OrderStatusBadge status={order.status} />
        </div>
        <p className="text-sm text-muted-foreground">
          {formatDateTime(order.created_at)} · {itemCount} ürün
        </p>
      </div>
      <div className="flex items-center justify-between gap-4 sm:justify-end">
        <span className="text-base font-semibold tabular-nums">{formatPrice(total, currency)}</span>
        <Button asChild variant="outline" size="sm">
          <Link href={`/orders/${order.id}`}>
            Detay
            <ChevronRight className="ml-1 h-4 w-4" />
          </Link>
        </Button>
      </div>
    </Card>
  );
}
