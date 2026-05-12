import Link from 'next/link';
import { ChevronLeft } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { AddToCartButton } from '@/components/cart/add-to-cart-button';
import { formatPrice } from '@/lib/currency/format';
import { SUPPORTED_CURRENCIES, type Currency } from '@/types/currency';
import type { Product } from '@/types/product';

interface ProductDetailProps {
  product: Product;
  currency: Currency;
}

export function ProductDetail({ product, currency }: ProductDetailProps) {
  const price = product.prices[currency] ?? product.price;
  const inStock = product.stock > 0;

  return (
    <main className="container mx-auto max-w-3xl px-4 py-8">
      <Button asChild variant="ghost" size="sm" className="mb-4">
        <Link href="/">
          <ChevronLeft className="mr-1 h-4 w-4" /> Tüm ürünlere dön
        </Link>
      </Button>

      <article className="space-y-6">
        <header className="space-y-2">
          <h1 className="text-3xl font-semibold tracking-tight">{product.name}</h1>
          <p className="leading-relaxed text-muted-foreground">{product.description}</p>
        </header>

        <section className="rounded-lg border bg-card p-6 shadow-sm">
          <div className="flex flex-wrap items-baseline justify-between gap-4">
            <span className="text-3xl font-semibold tracking-tight">
              {formatPrice(price, currency)}
            </span>
            <Badge variant={inStock ? 'secondary' : 'destructive'}>
              {inStock ? `${product.stock} adet stokta` : 'Stokta yok'}
            </Badge>
          </div>

          <Separator className="my-4" />

          <dl className="flex flex-wrap gap-x-6 gap-y-2 text-sm">
            {SUPPORTED_CURRENCIES.map((c) => (
              <div key={c} className="flex items-baseline gap-2">
                <dt className="text-muted-foreground">{c}</dt>
                <dd className="font-medium">
                  {formatPrice(product.prices[c] ?? product.price, c)}
                </dd>
              </div>
            ))}
          </dl>

          <Separator className="my-4" />

          <AddToCartButton
            productId={product.id}
            productName={product.name}
            stock={product.stock}
            size="lg"
            fullWidth
          />
        </section>
      </article>
    </main>
  );
}
