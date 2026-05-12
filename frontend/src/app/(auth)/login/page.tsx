import type { Metadata } from 'next';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { LoginForm } from './_components/login-form';

export const metadata: Metadata = {
  title: 'Giriş Yap',
  description: 'Dehasoft hesabınla giriş yap.',
};

interface LoginPageProps {
  searchParams: Promise<{ next?: string }>;
}

export default async function LoginPage({ searchParams }: LoginPageProps) {
  const params = await searchParams;

  return (
    <Card>
      <CardHeader className="space-y-1 text-center">
        <CardTitle className="text-2xl">Tekrar hoş geldin</CardTitle>
        <CardDescription>Hesabına giriş yap ve alışverişe devam et.</CardDescription>
      </CardHeader>
      <CardContent>
        <LoginForm nextPath={params.next ?? null} />
      </CardContent>
    </Card>
  );
}
