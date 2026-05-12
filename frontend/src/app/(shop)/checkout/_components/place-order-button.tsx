'use client';

import { CreditCard } from 'lucide-react';
import { apiClient } from '@/lib/api/client';
import { Button } from '@/components/ui/button';
import { useApiMutation } from '@/hooks/use-api-mutation';
import type { ApiSuccess } from '@/types/api';
import type { Order } from '@/types/order';

export function PlaceOrderButton() {
  const { mutate, pending } = useApiMutation<Order>({
    successMessage: (order) => `Sipariş #${order.id} oluşturuldu.`,
    navigateOnSuccess: (order) => `/orders/${order.id}`,
    errorFallback: 'Sipariş oluşturulamadı.',
  });

  const onClick = () =>
    mutate(async () => {
      const response = await apiClient<ApiSuccess<Order>>('/api/orders', { method: 'POST' });
      return response.data;
    });

  return (
    <Button type="button" size="lg" className="w-full" onClick={onClick} disabled={pending}>
      <CreditCard className="h-4 w-4" />
      {pending ? 'Sipariş oluşturuluyor…' : 'Siparişi Tamamla'}
    </Button>
  );
}
