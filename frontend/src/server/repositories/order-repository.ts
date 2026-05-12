import 'server-only';
import { laravel } from '@/server/http/laravel-client';
import type { ApiPaginated, ApiSuccess } from '@/types/api';
import type { Order } from '@/types/order';

export interface OrderListQuery {
  page?: number;
  perPage?: number;
}

function buildOrdersPath(query: OrderListQuery): string {
  const params = new URLSearchParams();
  if (query.page !== undefined) params.set('page', String(query.page));
  if (query.perPage !== undefined) params.set('per_page', String(query.perPage));
  const qs = params.toString();
  return qs ? `/api/v1/orders?${qs}` : '/api/v1/orders';
}

export const orderRepository = {
  async list(token: string, query: OrderListQuery = {}): Promise<ApiPaginated<Order>> {
    return laravel<ApiPaginated<Order>>(buildOrdersPath(query), { token });
  },

  async show(token: string, id: number): Promise<Order> {
    const response = await laravel<ApiSuccess<Order>>(`/api/v1/orders/${id}`, { token });
    return response.data;
  },

  async checkout(token: string): Promise<Order> {
    const response = await laravel<ApiSuccess<Order>>('/api/v1/orders', {
      method: 'POST',
      token,
    });
    return response.data;
  },

  async cancel(token: string, id: number): Promise<Order> {
    const response = await laravel<ApiSuccess<Order>>(`/api/v1/orders/${id}/cancel`, {
      method: 'POST',
      token,
    });
    return response.data;
  },

  async complete(token: string, id: number): Promise<Order> {
    const response = await laravel<ApiSuccess<Order>>(`/api/v1/orders/${id}/complete`, {
      method: 'POST',
      token,
    });
    return response.data;
  },
};
