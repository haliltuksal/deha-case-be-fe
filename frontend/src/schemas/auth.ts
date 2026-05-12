import { z } from 'zod';

export const loginSchema = z.object({
  email: z.string().email('Geçerli bir e-posta adresi girin.'),
  password: z.string().min(1, 'Şifrenizi girin.'),
});

export const registerSchema = z
  .object({
    name: z
      .string()
      .trim()
      .min(2, 'Ad en az 2 karakter olmalı.')
      .max(100, 'Ad en fazla 100 karakter olabilir.'),
    email: z.string().email('Geçerli bir e-posta adresi girin.'),
    password: z
      .string()
      .min(8, 'Şifre en az 8 karakter olmalı.')
      .max(72, 'Şifre en fazla 72 karakter olabilir.'),
    password_confirmation: z.string(),
  })
  .refine((data) => data.password === data.password_confirmation, {
    path: ['password_confirmation'],
    message: 'Şifreler eşleşmiyor.',
  });

export type LoginInput = z.infer<typeof loginSchema>;
export type RegisterInput = z.infer<typeof registerSchema>;
