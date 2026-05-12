'use client';

import { Trash2 } from 'lucide-react';
import { apiClient } from '@/lib/api/client';
import { Button } from '@/components/ui/button';
import { useApiMutation } from '@/hooks/use-api-mutation';

interface RemoveItemButtonProps {
  productId: number;
  productName: string;
}

export function RemoveItemButton({ productId, productName }: RemoveItemButtonProps) {
  const { mutate, pending } = useApiMutation({
    successMessage: `${productName} sepetten kaldırıldı.`,
    errorFallback: 'Ürün kaldırılamadı.',
  });

  const onClick = () =>
    mutate(() => apiClient(`/api/cart/items/${productId}`, { method: 'DELETE' }));

  return (
    <Button
      type="button"
      variant="ghost"
      size="icon"
      onClick={onClick}
      disabled={pending}
      aria-label={`${productName} ürününü kaldır`}
    >
      <Trash2 className="h-4 w-4" />
    </Button>
  );
}
