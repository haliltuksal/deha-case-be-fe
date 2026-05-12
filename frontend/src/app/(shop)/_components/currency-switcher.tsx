'use client';

import { useTransition } from 'react';
import { useRouter } from 'next/navigation';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { setCurrencyPreference } from '../_actions/currency';
import { isCurrency, SUPPORTED_CURRENCIES, type Currency } from '@/types/currency';

const CURRENCY_LABELS: Record<Currency, string> = {
  TRY: '₺ TRY',
  USD: '$ USD',
  EUR: '€ EUR',
};

interface CurrencySwitcherProps {
  value: Currency;
}

export function CurrencySwitcher({ value }: CurrencySwitcherProps) {
  const router = useRouter();
  const [pending, startTransition] = useTransition();

  const onValueChange = (next: string) => {
    if (!isCurrency(next) || next === value) return;
    startTransition(async () => {
      await setCurrencyPreference(next);
      router.refresh();
    });
  };

  return (
    <Select value={value} onValueChange={onValueChange} disabled={pending}>
      <SelectTrigger className="h-9 w-[80px] sm:w-[110px]" aria-label="Para birimi">
        <SelectValue />
      </SelectTrigger>
      <SelectContent>
        {SUPPORTED_CURRENCIES.map((currency) => (
          <SelectItem key={currency} value={currency}>
            {CURRENCY_LABELS[currency]}
          </SelectItem>
        ))}
      </SelectContent>
    </Select>
  );
}
