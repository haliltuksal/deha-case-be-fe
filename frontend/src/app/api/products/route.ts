import { NextResponse } from 'next/server';
import { parseJsonBody } from '@/server/bff/parse-body';
import { bffSuccess } from '@/server/bff/response';
import { withAdminToken, withErrorHandling } from '@/server/bff/route-helpers';
import { productRepository, type ProductListQuery } from '@/server/repositories/product-repository';
import { parsePositiveInt } from '@/lib/utils/parse-int';
import { productInputSchema } from '@/schemas/product';

export const runtime = 'nodejs';

export const GET = withErrorHandling(async (request) => {
  const url = new URL(request.url);
  const query: ProductListQuery = {};

  const page = parsePositiveInt(url.searchParams.get('page'));
  if (page !== null) query.page = page;

  const perPage = parsePositiveInt(url.searchParams.get('per_page'));
  if (perPage !== null) query.perPage = perPage;

  const search = url.searchParams.get('search');
  if (search && search.trim().length > 0) query.search = search.trim();

  const result = await productRepository.list(query);
  return NextResponse.json(result);
});

export const POST = withAdminToken(async (request, { token }) => {
  const input = await parseJsonBody(request, productInputSchema);
  const product = await productRepository.create(token, input);
  return bffSuccess(product, { status: 201, message: 'Ürün oluşturuldu.' });
});
