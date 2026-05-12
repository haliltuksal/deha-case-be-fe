import { NextResponse } from 'next/server';
import { parseJsonBody } from '@/server/bff/parse-body';
import { parseNumericParam } from '@/server/bff/parse-id';
import { bffSuccess } from '@/server/bff/response';
import { withAdminToken, withErrorHandling } from '@/server/bff/route-helpers';
import { productRepository } from '@/server/repositories/product-repository';
import { productInputSchema } from '@/schemas/product';

export const runtime = 'nodejs';

const NOT_FOUND_MESSAGE = 'Aradığınız ürün bulunamadı.';

export const GET = withErrorHandling<{ id: string }>(async (_request, { params }) => {
  const id = await parseNumericParam(params, 'id', NOT_FOUND_MESSAGE);
  const product = await productRepository.show(id);
  return bffSuccess(product);
});

export const PUT = withAdminToken<{ id: string }>(async (request, { token, params }) => {
  const id = await parseNumericParam(params, 'id', NOT_FOUND_MESSAGE);
  const input = await parseJsonBody(request, productInputSchema);
  const product = await productRepository.update(token, id, input);
  return bffSuccess(product, { message: 'Ürün güncellendi.' });
});

export const DELETE = withAdminToken<{ id: string }>(async (_request, { token, params }) => {
  const id = await parseNumericParam(params, 'id', NOT_FOUND_MESSAGE);
  await productRepository.destroy(token, id);
  return new NextResponse(null, { status: 204 });
});
