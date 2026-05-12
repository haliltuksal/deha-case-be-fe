import { NextResponse } from 'next/server';
import { parseNumericParam } from '@/server/bff/parse-id';
import { parseJsonBody } from '@/server/bff/parse-body';
import { bffSuccess } from '@/server/bff/response';
import { withToken } from '@/server/bff/route-helpers';
import { cartRepository } from '@/server/repositories/cart-repository';
import { updateCartItemSchema } from '@/schemas/cart';

export const runtime = 'nodejs';

const NOT_FOUND_MESSAGE = 'Aradığınız ürün sepette bulunamadı.';

export const PUT = withToken<{ productId: string }>(async (request, { token, params }) => {
  const productId = await parseNumericParam(params, 'productId', NOT_FOUND_MESSAGE);
  const input = await parseJsonBody(request, updateCartItemSchema);
  const cart = await cartRepository.updateItem(token, productId, { quantity: input.quantity });
  return bffSuccess(cart, { message: 'Sepet güncellendi.' });
});

export const DELETE = withToken<{ productId: string }>(async (_request, { token, params }) => {
  const productId = await parseNumericParam(params, 'productId', NOT_FOUND_MESSAGE);
  await cartRepository.removeItem(token, productId);
  return new NextResponse(null, { status: 204 });
});
