import { parseJsonBody } from '@/server/bff/parse-body';
import { bffSuccess } from '@/server/bff/response';
import { withToken } from '@/server/bff/route-helpers';
import { cartRepository } from '@/server/repositories/cart-repository';
import { addToCartSchema } from '@/schemas/cart';

export const runtime = 'nodejs';

export const POST = withToken(async (request, { token }) => {
  const input = await parseJsonBody(request, addToCartSchema);
  const cart = await cartRepository.addItem(token, {
    productId: input.product_id,
    quantity: input.quantity,
  });
  return bffSuccess(cart, { message: 'Ürün sepete eklendi.' });
});
