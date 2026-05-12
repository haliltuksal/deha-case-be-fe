import 'server-only';
import { laravel } from '@/server/http/laravel-client';
import type { ApiPaginated, ApiSuccess } from '@/types/api';
import type { Product, ProductInput } from '@/types/product';

export interface ProductListQuery {
  page?: number;
  perPage?: number;
  search?: string;
}

function buildProductsPath(query: ProductListQuery): string {
  const params = new URLSearchParams();
  if (query.page !== undefined) params.set('page', String(query.page));
  if (query.perPage !== undefined) params.set('per_page', String(query.perPage));
  if (query.search !== undefined && query.search.length > 0) params.set('search', query.search);
  const qs = params.toString();
  return qs ? `/api/v1/products?${qs}` : '/api/v1/products';
}

export const productRepository = {
  async list(query: ProductListQuery = {}): Promise<ApiPaginated<Product>> {
    return laravel<ApiPaginated<Product>>(buildProductsPath(query));
  },

  async show(id: number): Promise<Product> {
    const response = await laravel<ApiSuccess<Product>>(`/api/v1/products/${id}`);
    return response.data;
  },

  async create(token: string, input: ProductInput): Promise<Product> {
    const response = await laravel<ApiSuccess<Product>>('/api/v1/products', {
      method: 'POST',
      token,
      json: input,
    });
    return response.data;
  },

  async update(token: string, id: number, input: ProductInput): Promise<Product> {
    const response = await laravel<ApiSuccess<Product>>(`/api/v1/products/${id}`, {
      method: 'PUT',
      token,
      json: input,
    });
    return response.data;
  },

  async destroy(token: string, id: number): Promise<void> {
    await laravel<void>(`/api/v1/products/${id}`, {
      method: 'DELETE',
      token,
    });
  },
};
