import 'server-only';
import { laravel } from '@/server/http/laravel-client';
import type { ApiSuccess } from '@/types/api';
import type { Cart } from '@/types/cart';

export interface AddCartItemInput {
  productId: number;
  quantity: number;
}

export interface UpdateCartItemInput {
  quantity: number;
}

export const cartRepository = {
  async show(token: string): Promise<Cart> {
    const response = await laravel<ApiSuccess<Cart>>('/api/v1/cart', { token });
    return response.data;
  },

  async clear(token: string): Promise<void> {
    await laravel<void>('/api/v1/cart', { method: 'DELETE', token });
  },

  async addItem(token: string, input: AddCartItemInput): Promise<Cart> {
    const response = await laravel<ApiSuccess<Cart>>('/api/v1/cart/items', {
      method: 'POST',
      token,
      json: { product_id: input.productId, quantity: input.quantity },
    });
    return response.data;
  },

  async updateItem(token: string, productId: number, input: UpdateCartItemInput): Promise<Cart> {
    const response = await laravel<ApiSuccess<Cart>>(`/api/v1/cart/items/${productId}`, {
      method: 'PUT',
      token,
      json: { quantity: input.quantity },
    });
    return response.data;
  },

  async removeItem(token: string, productId: number): Promise<void> {
    await laravel<void>(`/api/v1/cart/items/${productId}`, { method: 'DELETE', token });
  },
};
