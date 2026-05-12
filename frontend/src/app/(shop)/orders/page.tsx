import type { Metadata } from 'next';
import { redirect } from 'next/navigation';
import { PaginationNav } from '@/components/ui/pagination-nav';
import { parsePage } from '@/lib/utils/parse-int';
import { readAuthCookie } from '@/server/auth/cookie';
import { getActiveCurrency } from '@/server/preferences/currency';
import { orderRepository } from '@/server/repositories/order-repository';
import { OrderListEmpty } from './_components/order-list-empty';
import { OrderRow } from './_components/order-row';

export const metadata: Metadata = { title: 'Siparişlerim' };
export const dynamic = 'force-dynamic';

const PAGE_SIZE = 10;

interface OrdersPageProps {
  searchParams: Promise<{ page?: string }>;
}

export default async function OrdersPage({ searchParams }: OrdersPageProps) {
  const token = await readAuthCookie();
  if (!token) {
    redirect('/login?next=/orders');
  }

  const params = await searchParams;
  const page = parsePage(params.page);

  const [response, currency] = await Promise.all([
    orderRepository.list(token, { page, perPage: PAGE_SIZE }),
    getActiveCurrency(),
  ]);
  const { items: orders, pagination } = response.data;

  const buildHref = (target: number): string => (target > 1 ? `/orders?page=${target}` : '/orders');

  return (
    <main className="container mx-auto max-w-4xl px-4 py-8">
      <header className="mb-6 space-y-1">
        <h1 className="text-2xl font-semibold tracking-tight">Siparişlerim</h1>
        <p className="text-sm text-muted-foreground">
          {pagination.total} sipariş · sayfa {pagination.current_page} / {pagination.last_page || 1}
        </p>
      </header>

      {orders.length === 0 ? (
        <OrderListEmpty />
      ) : (
        <>
          <div className="space-y-3">
            {orders.map((order) => (
              <OrderRow key={order.id} order={order} currency={currency} />
            ))}
          </div>
          <PaginationNav pagination={pagination} buildHref={buildHref} />
        </>
      )}
    </main>
  );
}
