import type { Metadata } from 'next';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { RegisterForm } from './_components/register-form';

export const metadata: Metadata = {
  title: 'Hesap Oluştur',
  description: 'Dehasoft hesabı oluştur ve alışverişe başla.',
};

export default function RegisterPage() {
  return (
    <Card>
      <CardHeader className="space-y-1 text-center">
        <CardTitle className="text-2xl">Hesap Oluştur</CardTitle>
        <CardDescription>Birkaç saniye içinde hesabını hazırla.</CardDescription>
      </CardHeader>
      <CardContent>
        <RegisterForm />
      </CardContent>
    </Card>
  );
}
