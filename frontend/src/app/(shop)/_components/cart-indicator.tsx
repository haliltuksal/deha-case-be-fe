import Link from 'next/link';
import { ShoppingCart } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

interface CartIndicatorProps {
  count: number;
}

export function CartIndicator({ count }: CartIndicatorProps) {
  const showBadge = count > 0;
  const label = showBadge ? `Sepetim, ${count} ürün` : 'Sepetim';

  return (
    <Button asChild variant="ghost" size="sm" className="relative" aria-label={label}>
      <Link href="/cart">
        <ShoppingCart className="h-4 w-4" />
        <span className="hidden sm:inline">Sepet</span>
        {showBadge && (
          <Badge
            variant="secondary"
            className="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full px-1 text-xs"
          >
            {count > 99 ? '99+' : count}
          </Badge>
        )}
      </Link>
    </Button>
  );
}
