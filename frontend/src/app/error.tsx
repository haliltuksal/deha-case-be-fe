'use client';

import { useEffect } from 'react';
import Link from 'next/link';
import { AlertTriangle } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface ErrorProps {
  error: Error & { digest?: string };
  reset: () => void;
}

export default function GlobalError({ error, reset }: ErrorProps) {
  useEffect(() => {
    console.error('[app] unhandled error', error);
  }, [error]);

  return (
    <main className="container mx-auto flex min-h-[60vh] max-w-xl flex-col items-center justify-center gap-4 px-4 text-center">
      <AlertTriangle className="h-12 w-12 text-destructive" aria-hidden />
      <div className="space-y-2">
        <h1 className="text-2xl font-semibold tracking-tight">Bir şeyler ters gitti</h1>
        <p className="text-muted-foreground">
          Beklenmeyen bir hata oluştu. Sayfayı yenilemeyi deneyebilirsin.
        </p>
      </div>
      <div className="flex flex-wrap items-center gap-2">
        <Button type="button" onClick={reset}>
          Tekrar dene
        </Button>
        <Button asChild variant="outline">
          <Link href="/">Anasayfaya dön</Link>
        </Button>
      </div>
    </main>
  );
}
