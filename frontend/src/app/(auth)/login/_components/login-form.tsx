'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { zodResolver } from '@hookform/resolvers/zod';
import { useForm } from 'react-hook-form';
import { apiClient } from '@/lib/api/client';
import { handleFormSubmitError } from '@/lib/forms/map-api-errors';
import { sanitiseNextPath } from '@/lib/utils/sanitise-next';
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
import { loginSchema, type LoginInput } from '@/schemas/auth';

const LOGIN_FIELDS = ['email', 'password'] as const;

interface LoginFormProps {
  nextPath?: string | null;
}

export function LoginForm({ nextPath }: LoginFormProps = {}) {
  const router = useRouter();
  const safeNext = sanitiseNextPath(nextPath ?? null);

  const form = useForm<LoginInput>({
    resolver: zodResolver(loginSchema),
    defaultValues: { email: '', password: '' },
  });

  const onSubmit = form.handleSubmit(async (values) => {
    try {
      await apiClient('/api/auth/login', { method: 'POST', json: values });
      router.replace(safeNext ?? '/');
      router.refresh();
    } catch (error) {
      handleFormSubmitError(error, form, LOGIN_FIELDS);
    }
  });

  return (
    <Form {...form}>
      <form onSubmit={onSubmit} className="space-y-4" noValidate>
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
                <Input type="password" autoComplete="current-password" {...field} />
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
          {form.formState.isSubmitting ? 'Giriş yapılıyor…' : 'Giriş Yap'}
        </Button>
        <p className="text-center text-sm text-muted-foreground">
          Hesabın yok mu?{' '}
          <Link href="/register" className="font-medium text-foreground hover:underline">
            Hesap oluştur
          </Link>
        </p>
      </form>
    </Form>
  );
}
