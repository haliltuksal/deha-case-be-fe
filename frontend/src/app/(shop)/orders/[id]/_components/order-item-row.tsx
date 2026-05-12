import Link from 'next/link';
import { formatPrice } from '@/lib/currency/format';
import type { Currency } from '@/types/currency';
import type { OrderItem } from '@/types/order';

interface OrderItemRowProps {
  item: OrderItem;
  currency: Currency;
}

export function OrderItemRow({ item, currency }: OrderItemRowProps) {
  const lineTotal = item.line_totals[currency] ?? item.line_total;
  const unitPrice = item.unit_prices[currency] ?? item.unit_price;
  const productLink = item.product_id !== null ? `/products/${item.product_id}` : null;

  return (
    <div className="flex flex-col gap-1 border-b py-3 last:border-b-0 sm:flex-row sm:items-center sm:justify-between">
      <div className="min-w-0 flex-1 space-y-1">
        {productLink ? (
          <Link href={productLink} className="line-clamp-1 text-base font-medium hover:underline">
            {item.name}
          </Link>
        ) : (
          <span className="line-clamp-1 text-base font-medium italic text-muted-foreground">
            {item.name}
          </span>
        )}
        <p className="text-sm text-muted-foreground">
          {item.quantity} adet · Birim {formatPrice(unitPrice, currency)}
          {productLink === null && (
            <span className="ml-2 text-xs">(ürün artık katalogda değil)</span>
          )}
        </p>
      </div>
      <span className="text-base font-semibold tabular-nums sm:text-right">
        {formatPrice(lineTotal, currency)}
      </span>
    </div>
  );
}
