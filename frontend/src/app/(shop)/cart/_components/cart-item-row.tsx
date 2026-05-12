import Link from 'next/link';
import { formatPrice } from '@/lib/currency/format';
import type { Currency } from '@/types/currency';
import type { CartItem } from '@/types/cart';
import { QuantityStepper } from './quantity-stepper';
import { RemoveItemButton } from './remove-item-button';

interface CartItemRowProps {
  item: CartItem;
  currency: Currency;
}

export function CartItemRow({ item, currency }: CartItemRowProps) {
  const lineTotal = item.line_totals[currency] ?? item.line_total;
  const unitPrice = item.unit_prices[currency] ?? item.unit_price;

  return (
    <div className="flex flex-col gap-3 rounded-lg border bg-card p-4 sm:flex-row sm:items-center sm:justify-between">
      <div className="min-w-0 flex-1 space-y-1">
        <Link
          href={`/products/${item.product_id}`}
          className="line-clamp-1 text-base font-medium hover:underline"
        >
          {item.name}
        </Link>
        <p className="text-sm text-muted-foreground">
          Birim: {formatPrice(unitPrice, currency)}
          <span className="px-2 text-muted-foreground/50">·</span>
          Stokta {item.available_stock} adet
        </p>
      </div>
      <div className="flex items-center justify-between gap-4 sm:justify-end">
        <QuantityStepper
          productId={item.product_id}
          quantity={item.quantity}
          max={item.available_stock}
        />
        <span className="min-w-[6rem] text-right text-base font-semibold tabular-nums">
          {formatPrice(lineTotal, currency)}
        </span>
        <RemoveItemButton productId={item.product_id} productName={item.name} />
      </div>
    </div>
  );
}
