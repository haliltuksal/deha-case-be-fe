import Link from 'next/link';
import { Pencil } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { formatPrice } from '@/lib/currency/format';
import type { Currency } from '@/types/currency';
import type { Product } from '@/types/product';
import { DeleteProductButton } from './delete-product-button';

interface AdminProductRowProps {
  product: Product;
  currency: Currency;
}

export function AdminProductRow({ product, currency }: AdminProductRowProps) {
  const price = product.prices[currency] ?? product.price;
  const inStock = product.stock > 0;

  return (
    <Card className="flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between">
      <div className="min-w-0 flex-1 space-y-1">
        <div className="flex flex-wrap items-center gap-3">
          <h3 className="line-clamp-1 text-base font-semibold">{product.name}</h3>
          <Badge variant={inStock ? 'secondary' : 'destructive'}>
            {inStock ? `${product.stock} adet` : 'Tükendi'}
          </Badge>
        </div>
        <p className="line-clamp-1 text-sm text-muted-foreground">{product.description}</p>
      </div>
      <div className="flex items-center justify-between gap-3 sm:justify-end">
        <span className="text-base font-semibold tabular-nums">{formatPrice(price, currency)}</span>
        <div className="flex items-center gap-1">
          <Button asChild variant="outline" size="sm">
            <Link href={`/admin/products/${product.id}/edit`}>
              <Pencil className="h-4 w-4" />
              Düzenle
            </Link>
          </Button>
          <DeleteProductButton productId={product.id} productName={product.name} />
        </div>
      </div>
    </Card>
  );
}
