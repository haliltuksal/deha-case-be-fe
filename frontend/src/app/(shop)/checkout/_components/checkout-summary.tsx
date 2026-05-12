import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { formatPrice } from '@/lib/currency/format';
import { SUPPORTED_CURRENCIES, type Currency } from '@/types/currency';
import type { Cart } from '@/types/cart';

interface CheckoutSummaryProps {
  cart: Cart;
  currency: Currency;
}

export function CheckoutSummary({ cart, currency }: CheckoutSummaryProps) {
  const headlineTotal = cart.totals[currency] ?? cart.totals.TRY;

  return (
    <Card>
      <CardHeader>
        <CardTitle>Sipariş Özeti</CardTitle>
      </CardHeader>
      <CardContent className="space-y-4">
        <ul className="space-y-2">
          {cart.items.map((item) => (
            <li key={item.product_id} className="flex items-baseline justify-between gap-3">
              <div className="min-w-0">
                <p className="line-clamp-1 text-sm font-medium">{item.name}</p>
                <p className="text-xs text-muted-foreground">{item.quantity} adet</p>
              </div>
              <span className="text-sm font-medium tabular-nums">
                {formatPrice(
                  item.subtotal[currency] ?? item.subtotal[item.unit_currency],
                  currency,
                )}
              </span>
            </li>
          ))}
        </ul>

        <Separator />

        <div className="flex items-baseline justify-between">
          <span className="text-sm text-muted-foreground">Toplam</span>
          <span className="text-2xl font-semibold tracking-tight">
            {formatPrice(headlineTotal, currency)}
          </span>
        </div>

        <dl className="flex flex-wrap gap-x-5 gap-y-1 text-xs text-muted-foreground">
          {SUPPORTED_CURRENCIES.filter((c) => c !== currency).map((c) => (
            <div key={c} className="flex items-baseline gap-1.5">
              <dt>{c}</dt>
              <dd className="font-medium text-foreground">
                {formatPrice(cart.totals[c] ?? cart.totals.TRY, c)}
              </dd>
            </div>
          ))}
        </dl>
      </CardContent>
    </Card>
  );
}
