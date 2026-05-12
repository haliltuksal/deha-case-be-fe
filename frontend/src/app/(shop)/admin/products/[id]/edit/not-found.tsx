import Link from 'next/link';
import { PackageX } from 'lucide-react';
import { Button } from '@/components/ui/button';

export default function EditProductNotFound() {
  return (
    <main className="container mx-auto flex max-w-xl flex-col items-center gap-4 px-4 py-16 text-center">
      <PackageX className="h-12 w-12 text-muted-foreground" aria-hidden />
      <div className="space-y-2">
        <h1 className="text-2xl font-semibold tracking-tight">Ürün bulunamadı</h1>
        <p className="text-muted-foreground">
          Düzenlemek istediğin ürün mevcut değil veya silinmiş olabilir.
        </p>
      </div>
      <Button asChild>
        <Link href="/admin/products">Ürünlere dön</Link>
      </Button>
    </main>
  );
}
