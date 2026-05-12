import { z } from 'zod';
import { SUPPORTED_CURRENCIES } from '@/types/currency';

const decimalRegex = /^\d+(\.\d{1,2})?$/;

export const productInputSchema = z.object({
  name: z
    .string()
    .trim()
    .min(2, 'Ad en az 2 karakter olmalı.')
    .max(255, 'Ad en fazla 255 karakter olabilir.'),
  description: z
    .string()
    .trim()
    .min(1, 'Açıklama girin.')
    .max(5000, 'Açıklama en fazla 5000 karakter olabilir.'),
  price: z.string().trim().regex(decimalRegex, 'Geçerli bir fiyat girin (örn. 99.99).'),
  base_currency: z.enum(SUPPORTED_CURRENCIES, {
    errorMap: () => ({ message: 'Geçerli bir para birimi seçin.' }),
  }),
  stock: z.coerce
    .number({ invalid_type_error: 'Stok bir sayı olmalı.' })
    .int('Stok tam sayı olmalı.')
    .min(0, 'Stok negatif olamaz.')
    .max(1_000_000, 'Stok bu kadar yüksek olamaz.'),
});

export type ProductFormValues = z.infer<typeof productInputSchema>;
