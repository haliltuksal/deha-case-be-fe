import type { Metadata } from 'next';
import Link from 'next/link';
import { Plus } from 'lucide-react';
import { EmptyState } from '@/components/products/empty-state';
import { ProductSearch } from '@/components/products/product-search';
import { Button } from '@/components/ui/button';
import { PaginationNav } from '@/components/ui/pagination-nav';
import { parsePage } from '@/lib/utils/parse-int';
import { getActiveCurrency } from '@/server/preferences/currency';
import { productRepository } from '@/server/repositories/product-repository';
import { AdminProductRow } from './_components/admin-product-row';

export const metadata: Metadata = { title: 'Admin · Ürünler' };
export const dynamic = 'force-dynamic';

const PAGE_SIZE = 12;

interface AdminProductsPageProps {
  searchParams: Promise<{ page?: string; search?: string }>;
}

export default async function AdminProductsPage({ searchParams }: AdminProductsPageProps) {
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
    return suffix ? `/admin/products?${suffix}` : '/admin/products';
  };

  return (
    <main className="container mx-auto max-w-5xl px-4 py-8">
      <header className="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 className="text-2xl font-semibold tracking-tight">Ürün Yönetimi</h1>
          <p className="text-sm text-muted-foreground">{pagination.total} ürün toplam</p>
        </div>
        <div className="flex flex-wrap items-center gap-2">
          <ProductSearch initialValue={search ?? ''} />
          <Button asChild>
            <Link href="/admin/products/new">
              <Plus className="h-4 w-4" />
              Yeni Ürün
            </Link>
          </Button>
        </div>
      </header>

      {products.length === 0 ? (
        <EmptyState search={search} />
      ) : (
        <>
          <div className="space-y-3">
            {products.map((product) => (
              <AdminProductRow key={product.id} product={product} currency={currency} />
            ))}
          </div>
          <PaginationNav pagination={pagination} buildHref={buildHref} />
        </>
      )}
    </main>
  );
}
