import Link from 'next/link';
import { PackageSearch } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface EmptyStateProps {
  search?: string;
}

export function EmptyState({ search }: EmptyStateProps) {
  return (
    <div className="flex flex-col items-center justify-center gap-3 rounded-lg border border-dashed bg-muted/20 px-6 py-16 text-center">
      <PackageSearch className="h-10 w-10 text-muted-foreground" aria-hidden />
      <p className="text-base font-medium">
        {search ? `"${search}" için ürün bulunamadı.` : 'Şu anda gösterilecek bir ürün bulunmuyor.'}
      </p>
      {search ? (
        <Button asChild variant="link">
          <Link href="/">Aramayı temizle</Link>
        </Button>
      ) : null}
    </div>
  );
}
