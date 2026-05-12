import { NextResponse } from 'next/server';
import { bffSuccess } from '@/server/bff/response';
import { withToken } from '@/server/bff/route-helpers';
import { cartRepository } from '@/server/repositories/cart-repository';

export const runtime = 'nodejs';

export const GET = withToken(async (_request, { token }) => {
  const cart = await cartRepository.show(token);
  return bffSuccess(cart);
});

export const DELETE = withToken(async (_request, { token }) => {
  await cartRepository.clear(token);
  return new NextResponse(null, { status: 204 });
});
