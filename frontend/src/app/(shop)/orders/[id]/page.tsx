import type { Metadata } from 'next';
import { notFound, redirect } from 'next/navigation';
import { CancelOrderButton } from './_components/cancel-order-button';
import { CompleteOrderButton } from './_components/complete-order-button';
import { OrderDetail } from './_components/order-detail';
import { readAuthCookie } from '@/server/auth/cookie';
import { getCurrentUser } from '@/server/auth/session';
import { isHttpError } from '@/server/http/http-error';
import { getActiveCurrency } from '@/server/preferences/currency';
import { orderRepository } from '@/server/repositories/order-repository';
import type { Order } from '@/types/order';

export const dynamic = 'force-dynamic';

interface OrderPageProps {
  params: Promise<{ id: string }>;
}

export async function generateMetadata({ params }: OrderPageProps): Promise<Metadata> {
  const { id } = await params;
  return { title: `Sipariş #${id}` };
}

export default async function OrderPage({ params }: OrderPageProps) {
  const { id: rawId } = await params;
  const id = Number.parseInt(rawId, 10);
  if (!Number.isInteger(id) || id <= 0) {
    notFound();
  }

  const token = await readAuthCookie();
  if (!token) {
    redirect(`/login?next=/orders/${id}`);
  }

  const [order, currency, user] = await Promise.all([
    loadOrder(token, id),
    getActiveCurrency(),
    getCurrentUser(),
  ]);

  if (!order) {
    notFound();
  }

  const canCancel = order.status === 'pending';
  const canComplete = order.status === 'pending' && user?.is_admin === true;

  const actions =
    canCancel || canComplete ? (
      <>
        {canCancel && <CancelOrderButton orderId={order.id} />}
        {canComplete && <CompleteOrderButton orderId={order.id} />}
      </>
    ) : null;

  return <OrderDetail order={order} currency={currency} actions={actions} />;
}

async function loadOrder(token: string, id: number): Promise<Order | null> {
  try {
    return await orderRepository.show(token, id);
  } catch (error) {
    if (isHttpError(error) && error.status === 404) {
      return null;
    }
    throw error;
  }
}
