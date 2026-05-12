'use client';

import { Minus, Plus } from 'lucide-react';
import { apiClient } from '@/lib/api/client';
import { Button } from '@/components/ui/button';
import { useApiMutation } from '@/hooks/use-api-mutation';

interface QuantityStepperProps {
  productId: number;
  quantity: number;
  max: number;
}

export function QuantityStepper({ productId, quantity, max }: QuantityStepperProps) {
  const { mutate, pending } = useApiMutation({ errorFallback: 'Miktar güncellenemedi.' });

  const update = (next: number) => {
    if (next < 1 || pending) return;
    mutate(() =>
      apiClient(`/api/cart/items/${productId}`, {
        method: 'PUT',
        json: { quantity: next },
      }),
    );
  };

  return (
    <div className="inline-flex items-center rounded-md border bg-background">
      <Button
        type="button"
        size="icon"
        variant="ghost"
        className="h-8 w-8 rounded-r-none"
        onClick={() => update(quantity - 1)}
        disabled={pending || quantity <= 1}
        aria-label="Bir azalt"
      >
        <Minus className="h-3.5 w-3.5" />
      </Button>
      <span
        className="min-w-8 px-2 text-center text-sm font-medium tabular-nums"
        aria-live="polite"
      >
        {quantity}
      </span>
      <Button
        type="button"
        size="icon"
        variant="ghost"
        className="h-8 w-8 rounded-l-none"
        onClick={() => update(quantity + 1)}
        disabled={pending || quantity >= max}
        aria-label="Bir arttır"
      >
        <Plus className="h-3.5 w-3.5" />
      </Button>
    </div>
  );
}
