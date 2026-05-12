'use client';

import { Button } from '@/components/ui/button';
import { ConfirmActionButton } from '@/components/ui/confirm-action-button';

interface CompleteOrderButtonProps {
  orderId: number;
}

export function CompleteOrderButton({ orderId }: CompleteOrderButtonProps) {
  return (
    <ConfirmActionButton
      trigger={<Button>Tamamlandı olarak işaretle</Button>}
      title="Sipariş tamamlanacak."
      description={'Bu işlem siparişin durumunu "Tamamlandı" olarak günceller. Geri alınamaz.'}
      actionLabel="Tamamla"
      pendingLabel="Güncelleniyor…"
      endpoint={`/api/orders/${orderId}/complete`}
      method="POST"
      successMessage="Sipariş tamamlandı olarak işaretlendi."
      errorFallback="Sipariş güncellenemedi."
    />
  );
}
