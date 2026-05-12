'use client';

import { usePathname, useRouter } from 'next/navigation';
import { ShoppingCart } from 'lucide-react';
import { apiClient } from '@/lib/api/client';
import { cn } from '@/lib/utils/cn';
import { Button } from '@/components/ui/button';
import { useApiMutation } from '@/hooks/use-api-mutation';

interface AddToCartButtonProps {
  productId: number;
  productName: string;
  stock: number;
  className?: string;
  size?: 'sm' | 'default' | 'lg';
  fullWidth?: boolean;
}

export function AddToCartButton({
  productId,
  productName,
  stock,
  className,
  size = 'default',
  fullWidth = false,
}: AddToCartButtonProps) {
  const router = useRouter();
  const pathname = usePathname();
  const outOfStock = stock <= 0;

  const { mutate, pending } = useApiMutation({
    successMessage: `${productName} sepete eklendi.`,
    errorFallback: 'Ürün sepete eklenemedi.',
    onApiError(error) {
      if (error.code === 'ERR_UNAUTHENTICATED' || error.status === 401) {
        router.push(`/login?next=${encodeURIComponent(pathname)}`);
        return true;
      }
    },
  });

  const onClick = () => {
    if (outOfStock || pending) return;
    mutate(() =>
      apiClient('/api/cart/items', {
        method: 'POST',
        json: { product_id: productId, quantity: 1 },
      }),
    );
  };

  return (
    <Button
      type="button"
      onClick={onClick}
      disabled={outOfStock || pending}
      size={size}
      className={cn(fullWidth && 'w-full', className)}
    >
      <ShoppingCart className="h-4 w-4" />
      {outOfStock ? 'Tükendi' : pending ? 'Ekleniyor…' : 'Sepete Ekle'}
    </Button>
  );
}
