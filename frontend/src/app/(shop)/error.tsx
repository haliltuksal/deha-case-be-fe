'use client';

import { useEffect } from 'react';
import Link from 'next/link';
import { AlertTriangle } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface ErrorProps {
  error: Error & { digest?: string };
  reset: () => void;
}

export default function ShopError({ error, reset }: ErrorProps) {
  useEffect(() => {
    console.error('[shop] segment error', error);
  }, [error]);

  return (
    <main className="container mx-auto flex min-h-[60vh] max-w-xl flex-col items-center justify-center gap-4 px-4 text-center">
      <AlertTriangle className="h-12 w-12 text-destructive" aria-hidden />
      <div className="space-y-2">
        <h1 className="text-xl font-semibold tracking-tight">Bu sayfa yüklenemedi</h1>
        <p className="text-sm text-muted-foreground">
          Sunucuyla iletişimde geçici bir sorun olabilir. Birkaç saniye sonra tekrar dene.
        </p>
      </div>
      <div className="flex flex-wrap items-center gap-2">
        <Button type="button" onClick={reset}>
          Tekrar dene
        </Button>
        <Button asChild variant="outline">
          <Link href="/">Ürünlere dön</Link>
        </Button>
      </div>
    </main>
  );
}
