import type { Metadata } from 'next';
import { EmptyState } from '@/components/products/empty-state';
import { ProductSearch } from '@/components/products/product-search';
import { PaginationNav } from '@/components/ui/pagination-nav';
import { parsePage } from '@/lib/utils/parse-int';
import { getActiveCurrency } from '@/server/preferences/currency';
import { productRepository } from '@/server/repositories/product-repository';
import { ProductList } from './_components/product-list';

export const metadata: Metadata = {
  title: 'Ürünler',
};

export const dynamic = 'force-dynamic';

const PAGE_SIZE = 12;

interface HomePageProps {
  searchParams: Promise<{ page?: string; search?: string }>;
}

export default async function HomePage({ searchParams }: HomePageProps) {
  const params = await searchParams;
  const page = parsePage(params.page);
  const search = params.search?.trim() || undefined;

  const [response, currency] = await Promise.all([
    productRepository.list({ page, perPage: PAGE_SIZE, search }),
    getActiveCurrency(),
  ]);
  const { items: products, pagination } = response.data;

  const buildHref = (target: number): string => {
    const qs = new URLSearchParams();
    if (search) qs.set('search', search);
    if (target > 1) qs.set('page', String(target));
    const suffix = qs.toString();
    return suffix ? `/?${suffix}` : '/';
  };

  return (
    <main className="container mx-auto flex flex-col gap-6 px-4 py-8">
      <div className="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
        <div>
          <h1 className="text-2xl font-semibold tracking-tight">Ürünler</h1>
          <p className="text-sm text-muted-foreground">
            {search ? (
              <>
                <span className="font-medium text-foreground">&ldquo;{search}&rdquo;</span> için
                {` ${pagination.total} sonuç`}
              </>
            ) : (
              <>{pagination.total} ürün listeleniyor</>
            )}
          </p>
        </div>
        <ProductSearch initialValue={search ?? ''} />
      </div>

      {products.length === 0 ? (
        <EmptyState search={search} />
      ) : (
        <>
          <ProductList products={products} currency={currency} />
          <PaginationNav pagination={pagination} buildHref={buildHref} />
        </>
      )}
    </main>
  );
}
