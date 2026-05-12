'use client';

import { Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { ConfirmActionButton } from '@/components/ui/confirm-action-button';

interface DeleteProductButtonProps {
  productId: number;
  productName: string;
}

export function DeleteProductButton({ productId, productName }: DeleteProductButtonProps) {
  return (
    <ConfirmActionButton
      trigger={
        <Button type="button" variant="ghost" size="icon" aria-label={`${productName} ürününü sil`}>
          <Trash2 className="h-4 w-4 text-destructive" />
        </Button>
      }
      title={`"${productName}" silinecek`}
      description="Ürün katalogdan kaldırılır. Bu üründen oluşturulmuş geçmiş siparişler korunur, ancak ürüne tıklanamaz hale gelir. Bu işlem geri alınamaz."
      actionLabel="Sil"
      pendingLabel="Siliniyor…"
      endpoint={`/api/products/${productId}`}
      method="DELETE"
      successMessage={`${productName} silindi.`}
      errorFallback="Ürün silinemedi."
    />
  );
}
