import Link from 'next/link';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { AddToCartButton } from '@/components/cart/add-to-cart-button';
import { formatPrice } from '@/lib/currency/format';
import type { Currency } from '@/types/currency';
import type { Product } from '@/types/product';

interface ProductCardProps {
  product: Product;
  currency: Currency;
}

export function ProductCard({ product, currency }: ProductCardProps) {
  const price = product.prices[currency] ?? product.price;
  const inStock = product.stock > 0;

  return (
    <Card className="flex h-full flex-col">
      <CardHeader>
        <CardTitle className="line-clamp-1 text-base">{product.name}</CardTitle>
      </CardHeader>
      <CardContent className="flex-1 space-y-3">
        <p className="line-clamp-2 text-sm text-muted-foreground">{product.description}</p>
        <div className="flex items-baseline justify-between gap-2">
          <span className="text-xl font-semibold tracking-tight">
            {formatPrice(price, currency)}
          </span>
          <Badge variant={inStock ? 'secondary' : 'destructive'}>
            {inStock ? `${product.stock} adet` : 'Tükendi'}
          </Badge>
        </div>
      </CardContent>
      <CardFooter className="flex gap-2">
        <Button asChild variant="outline" size="sm" className="flex-1">
          <Link href={`/products/${product.id}`}>Detay</Link>
        </Button>
        <AddToCartButton
          productId={product.id}
          productName={product.name}
          stock={product.stock}
          size="sm"
          className="flex-1"
        />
      </CardFooter>
    </Card>
  );
}
