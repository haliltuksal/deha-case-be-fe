import Link from 'next/link';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { formatPrice } from '@/lib/currency/format';
import { SUPPORTED_CURRENCIES, type Currency } from '@/types/currency';
import type { Cart } from '@/types/cart';

interface CartSummaryProps {
  cart: Cart;
  currency: Currency;
}

export function CartSummary({ cart, currency }: CartSummaryProps) {
  const headlineTotal = cart.totals[currency] ?? cart.subtotal;
  const otherCurrencies = SUPPORTED_CURRENCIES.filter((c) => c !== currency);

  return (
    <Card className="sticky top-20">
      <CardHeader>
        <CardTitle>Sipariş Özeti</CardTitle>
      </CardHeader>
      <CardContent className="space-y-4">
        <div className="flex items-baseline justify-between">
          <span className="text-sm text-muted-foreground">Ara toplam</span>
          <span className="text-2xl font-semibold tracking-tight">
            {formatPrice(headlineTotal, currency)}
          </span>
        </div>
        <Separator />
        <dl className="space-y-1.5 text-sm text-muted-foreground">
          {otherCurrencies.map((c) => (
            <div key={c} className="flex items-baseline justify-between">
              <dt>{c}</dt>
              <dd className="font-medium text-foreground">
                {formatPrice(cart.totals[c] ?? cart.subtotal, c)}
              </dd>
            </div>
          ))}
        </dl>
        <p className="text-xs text-muted-foreground">
          Toplam {cart.item_count} ürün · Sipariş oluşturduğunda canlı kur ile dönüşüm tamamlanır.
        </p>
      </CardContent>
      <CardFooter>
        <Button asChild className="w-full" size="lg">
          <Link href="/checkout">Siparişi Onayla</Link>
        </Button>
      </CardFooter>
    </Card>
  );
}
