import { NextResponse } from 'next/server';
import { bffSuccess } from '@/server/bff/response';
import { withToken } from '@/server/bff/route-helpers';
import { orderRepository, type OrderListQuery } from '@/server/repositories/order-repository';
import { parsePositiveInt } from '@/lib/utils/parse-int';

export const runtime = 'nodejs';

export const GET = withToken(async (request, { token }) => {
  const url = new URL(request.url);
  const query: OrderListQuery = {};

  const page = parsePositiveInt(url.searchParams.get('page'));
  if (page !== null) query.page = page;

  const perPage = parsePositiveInt(url.searchParams.get('per_page'));
  if (perPage !== null) query.perPage = perPage;

  // Repository returns the full ApiPaginated envelope from Laravel; forward
  // it as-is so the BFF surface mirrors the upstream shape.
  const result = await orderRepository.list(token, query);
  return NextResponse.json(result);
});

export const POST = withToken(async (_request, { token }) => {
  const order = await orderRepository.checkout(token);
  return bffSuccess(order, {
    status: 201,
    message: `Sipariş #${order.id} oluşturuldu.`,
  });
});
