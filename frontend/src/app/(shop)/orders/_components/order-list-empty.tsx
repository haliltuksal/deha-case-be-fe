import Link from 'next/link';
import { ScrollText } from 'lucide-react';
import { Button } from '@/components/ui/button';

export function OrderListEmpty() {
  return (
    <div className="flex flex-col items-center justify-center gap-4 rounded-lg border border-dashed bg-muted/20 px-6 py-20 text-center">
      <ScrollText className="h-12 w-12 text-muted-foreground" aria-hidden />
      <div className="space-y-1">
        <h2 className="text-lg font-semibold">Henüz siparişiniz yok</h2>
        <p className="text-sm text-muted-foreground">
          Sepetinize ürün ekleyip ilk siparişinizi oluşturabilirsiniz.
        </p>
      </div>
      <Button asChild>
        <Link href="/">Ürünleri keşfet</Link>
      </Button>
    </div>
  );
}
