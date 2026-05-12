'use client';

import { Button } from '@/components/ui/button';
import { ConfirmActionButton } from '@/components/ui/confirm-action-button';

export function ClearCartButton() {
  return (
    <ConfirmActionButton
      trigger={
        <Button variant="ghost" size="sm" className="text-muted-foreground">
          Sepeti Temizle
        </Button>
      }
      title="Sepeti temizlemek istediğine emin misin?"
      description="Sepetindeki tüm ürünler kaldırılacak. Bu işlem geri alınamaz."
      actionLabel="Temizle"
      pendingLabel="Temizleniyor…"
      endpoint="/api/cart"
      method="DELETE"
      successMessage="Sepet temizlendi."
      errorFallback="Sepet temizlenemedi."
    />
  );
}
