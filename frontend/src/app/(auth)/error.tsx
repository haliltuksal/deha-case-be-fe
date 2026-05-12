'use client';

import { useEffect } from 'react';
import Link from 'next/link';
import { AlertTriangle } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface ErrorProps {
  error: Error & { digest?: string };
  reset: () => void;
}

export default function AuthError({ error, reset }: ErrorProps) {
  useEffect(() => {
    console.error('[auth] segment error', error);
  }, [error]);

  return (
    <div className="flex flex-col items-center gap-4 rounded-lg border bg-card p-6 text-center shadow-sm">
      <AlertTriangle className="h-10 w-10 text-destructive" aria-hidden />
      <div className="space-y-1">
        <h1 className="text-lg font-semibold">Form yüklenemedi</h1>
        <p className="text-sm text-muted-foreground">
          Lütfen tekrar dene. Sorun devam ederse anasayfadan ulaşmayı dene.
        </p>
      </div>
      <div className="flex items-center gap-2">
        <Button type="button" size="sm" onClick={reset}>
          Tekrar dene
        </Button>
        <Button asChild size="sm" variant="outline">
          <Link href="/">Anasayfa</Link>
        </Button>
      </div>
    </div>
  );
}
