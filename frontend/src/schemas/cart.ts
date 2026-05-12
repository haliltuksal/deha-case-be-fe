import { z } from 'zod';

export const addToCartSchema = z.object({
  product_id: z.coerce.number().int().positive('Geçersiz ürün.'),
  quantity: z.coerce.number().int().min(1, 'Miktar en az 1 olmalı.'),
});

export const updateCartItemSchema = z.object({
  quantity: z.coerce.number().int().min(1, 'Miktar en az 1 olmalı.'),
});

export type AddToCartInput = z.infer<typeof addToCartSchema>;
export type UpdateCartItemInput = z.infer<typeof updateCartItemSchema>;
