import type { Currency } from '@/types/currency';
import type { Product } from '@/types/product';
import { ProductCard } from './product-card';

interface ProductListProps {
  products: ReadonlyArray<Product>;
  currency: Currency;
}

export function ProductList({ products, currency }: ProductListProps) {
  return (
    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      {products.map((product) => (
        <ProductCard key={product.id} product={product} currency={currency} />
      ))}
    </div>
  );
}
