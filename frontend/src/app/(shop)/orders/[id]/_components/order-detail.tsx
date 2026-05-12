import type { ReactNode } from 'react';
import Link from 'next/link';
import { ChevronLeft } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { formatPrice } from '@/lib/currency/format';
import { formatDateTime } from '@/lib/utils/format-date';
import { SUPPORTED_CURRENCIES, type Currency } from '@/types/currency';
import type { Order } from '@/types/order';
import { OrderStatusBadge } from '../../_components/order-status-badge';
import { OrderItemRow } from './order-item-row';

interface OrderDetailProps {
  order: Order;
  currency: Currency;
  actions?: ReactNode;
}

export function OrderDetail({ order, currency, actions }: OrderDetailProps) {
  const total = order.totals[currency] ?? order.total_amount;
  const otherCurrencies = SUPPORTED_CURRENCIES.filter((c) => c !== currency);
  const itemCount = order.items.reduce((sum, item) => sum + item.quantity, 0);

  return (
    <main className="container mx-auto max-w-4xl px-4 py-8">
      <Button asChild variant="ghost" size="sm" className="mb-4">
        <Link href="/orders">
          <ChevronLeft className="mr-1 h-4 w-4" /> Tüm siparişler
        </Link>
      </Button>

      <header className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div className="space-y-1">
          <h1 className="text-2xl font-semibold tracking-tight">Sipariş #{order.id}</h1>
          <p className="text-sm text-muted-foreground">
            {formatDateTime(order.created_at)} · {itemCount} ürün
          </p>
        </div>
        <OrderStatusBadge status={order.status} className="self-start text-sm" />
      </header>

      <Card className="mt-6">
        <CardHeader className="text-base font-semibold">Sipariş Kalemleri</CardHeader>
        <CardContent className="pt-0">
          {order.items.map((item, index) => (
            <OrderItemRow
              key={item.product_id ?? `removed-${index}`}
              item={item}
              currency={currency}
            />
          ))}
        </CardContent>
      </Card>

      <Card className="mt-6">
        <CardContent className="space-y-4 pt-6">
          <div className="flex items-baseline justify-between">
            <span className="text-sm text-muted-foreground">Toplam</span>
            <span className="text-2xl font-semibold tracking-tight">
              {formatPrice(total, currency)}
            </span>
          </div>
          <Separator />
          <dl className="flex flex-wrap gap-x-6 gap-y-2 text-sm text-muted-foreground">
            {otherCurrencies.map((c) => (
              <div key={c} className="flex items-baseline gap-2">
                <dt>{c}</dt>
                <dd className="font-medium text-foreground">
                  {formatPrice(order.totals[c] ?? order.total_amount, c)}
                </dd>
              </div>
            ))}
          </dl>
        </CardContent>
      </Card>

      {actions ? <div className="mt-6 flex flex-wrap items-center gap-3">{actions}</div> : null}
    </main>
  );
}
