import Link from 'next/link';
import { ShoppingBag } from 'lucide-react';
import { Button } from '@/components/ui/button';

export function EmptyCart() {
  return (
    <div className="flex flex-col items-center justify-center gap-4 rounded-lg border border-dashed bg-muted/20 px-6 py-20 text-center">
      <ShoppingBag className="h-12 w-12 text-muted-foreground" aria-hidden />
      <div className="space-y-1">
        <h2 className="text-lg font-semibold">Sepetiniz boş</h2>
        <p className="text-sm text-muted-foreground">
          Sepete ekleyeceğiniz ürünleri katalogdan keşfedin.
        </p>
      </div>
      <Button asChild>
        <Link href="/">Ürünleri keşfet</Link>
      </Button>
    </div>
  );
}
