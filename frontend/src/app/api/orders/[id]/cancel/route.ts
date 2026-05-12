import { parseNumericParam } from '@/server/bff/parse-id';
import { bffSuccess } from '@/server/bff/response';
import { withToken } from '@/server/bff/route-helpers';
import { orderRepository } from '@/server/repositories/order-repository';

export const runtime = 'nodejs';

export const POST = withToken<{ id: string }>(async (_request, { token, params }) => {
  const id = await parseNumericParam(params, 'id', 'Aradığınız sipariş bulunamadı.');
  const order = await orderRepository.cancel(token, id);
  return bffSuccess(order, { message: 'Sipariş iptal edildi.' });
});
