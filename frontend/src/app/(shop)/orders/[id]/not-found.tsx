import Link from 'next/link';
import { PackageX } from 'lucide-react';
import { Button } from '@/components/ui/button';

export default function OrderNotFound() {
  return (
    <main className="container mx-auto flex max-w-xl flex-col items-center gap-4 px-4 py-16 text-center">
      <PackageX className="h-12 w-12 text-muted-foreground" aria-hidden />
      <div className="space-y-2">
        <h1 className="text-2xl font-semibold tracking-tight">Sipariş bulunamadı</h1>
        <p className="text-muted-foreground">
          Aradığınız sipariş mevcut değil veya görüntüleme yetkiniz yok.
        </p>
      </div>
      <Button asChild>
        <Link href="/orders">Tüm siparişlere dön</Link>
      </Button>
    </main>
  );
}
