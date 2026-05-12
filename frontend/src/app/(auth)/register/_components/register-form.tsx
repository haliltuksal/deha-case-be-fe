'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { zodResolver } from '@hookform/resolvers/zod';
import { useForm } from 'react-hook-form';
import { apiClient } from '@/lib/api/client';
import { handleFormSubmitError } from '@/lib/forms/map-api-errors';
import { Button } from '@/components/ui/button';
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { registerSchema, type RegisterInput } from '@/schemas/auth';

const REGISTER_FIELDS = ['name', 'email', 'password', 'password_confirmation'] as const;

export function RegisterForm() {
  const router = useRouter();

  const form = useForm<RegisterInput>({
    resolver: zodResolver(registerSchema),
    defaultValues: {
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
    },
  });

  const onSubmit = form.handleSubmit(async (values) => {
    try {
      await apiClient('/api/auth/register', { method: 'POST', json: values });
      router.replace('/');
      router.refresh();
    } catch (error) {
      handleFormSubmitError(error, form, REGISTER_FIELDS);
    }
  });

  return (
    <Form {...form}>
      <form onSubmit={onSubmit} className="space-y-4" noValidate>
        <FormField
          control={form.control}
          name="name"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Ad Soyad</FormLabel>
              <FormControl>
                <Input autoComplete="name" placeholder="Halil Tuksal" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
        <FormField
          control={form.control}
          name="email"
          render={({ field }) => (
            <FormItem>
              <FormLabel>E-posta</FormLabel>
              <FormControl>
                <Input
                  type="email"
                  inputMode="email"
                  autoComplete="email"
                  placeholder="ornek@dehasoft.test"
                  {...field}
                />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
        <FormField
          control={form.control}
          name="password"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Şifre</FormLabel>
              <FormControl>
                <Input type="password" autoComplete="new-password" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
        <FormField
          control={form.control}
          name="password_confirmation"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Şifre (Tekrar)</FormLabel>
              <FormControl>
                <Input type="password" autoComplete="new-password" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
        <Button
          type="submit"
          className="w-full"
          disabled={form.formState.isSubmitting}
          aria-busy={form.formState.isSubmitting}
        >
          {form.formState.isSubmitting ? 'Hesap oluşturuluyor…' : 'Hesap Oluştur'}
        </Button>
        <p className="text-center text-sm text-muted-foreground">
          Zaten hesabın var mı?{' '}
          <Link href="/login" className="font-medium text-foreground hover:underline">
            Giriş yap
          </Link>
        </p>
      </form>
    </Form>
  );
}
