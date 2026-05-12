'use client';

import { Button } from '@/components/ui/button';
import { ConfirmActionButton } from '@/components/ui/confirm-action-button';

interface CancelOrderButtonProps {
  orderId: number;
}

export function CancelOrderButton({ orderId }: CancelOrderButtonProps) {
  return (
    <ConfirmActionButton
      trigger={<Button variant="outline">Siparişi İptal Et</Button>}
      title="Siparişi iptal etmek istediğine emin misin?"
      description="Sipariş iptal edilecek ve ürünlerin stoğu otomatik olarak geri yüklenecek."
      actionLabel="Siparişi İptal Et"
      pendingLabel="İptal ediliyor…"
      endpoint={`/api/orders/${orderId}/cancel`}
      method="POST"
      successMessage="Sipariş iptal edildi."
      errorFallback="Sipariş iptal edilemedi."
    />
  );
}
