'use client';

import { useEffect, useRef, useState } from 'react';
import { usePathname, useRouter, useSearchParams } from 'next/navigation';
import { Search } from 'lucide-react';
import { Input } from '@/components/ui/input';

const DEBOUNCE_MS = 400;

interface ProductSearchProps {
  initialValue: string;
}

export function ProductSearch({ initialValue }: ProductSearchProps) {
  const router = useRouter();
  const pathname = usePathname();
  const searchParams = useSearchParams();
  const [value, setValue] = useState(initialValue);
  const lastAppliedRef = useRef(initialValue);

  useEffect(() => {
    const trimmed = value.trim();
    if (trimmed === lastAppliedRef.current) {
      return;
    }

    const timer = setTimeout(() => {
      lastAppliedRef.current = trimmed;
      const params = new URLSearchParams(searchParams.toString());
      if (trimmed) {
        params.set('search', trimmed);
      } else {
        params.delete('search');
      }
      params.delete('page');
      const qs = params.toString();
      router.replace(qs ? `${pathname}?${qs}` : pathname);
    }, DEBOUNCE_MS);

    return () => clearTimeout(timer);
  }, [value, pathname, router, searchParams]);

  return (
    <div className="relative w-full max-w-sm">
      <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
      <Input
        type="search"
        inputMode="search"
        placeholder="Ürün ara…"
        value={value}
        onChange={(event) => setValue(event.target.value)}
        className="pl-9"
        aria-label="Ürün ara"
      />
    </div>
  );
}
