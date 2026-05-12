'use client';

import { useRouter } from 'next/navigation';
import { useTransition } from 'react';
import { LogOut } from 'lucide-react';
import { apiClient } from '@/lib/api/client';
import { DropdownMenuItem } from '@/components/ui/dropdown-menu';

export function LogoutButton() {
  const router = useRouter();
  const [pending, startTransition] = useTransition();

  const onSelect = (event: Event) => {
    event.preventDefault();
    startTransition(async () => {
      try {
        await apiClient('/api/auth/logout', { method: 'POST' });
      } catch {
        // The BFF clears the cookie even when the upstream call fails,
        // so we proceed to the post-logout state regardless.
      }
      router.replace('/');
      router.refresh();
    });
  };

  return (
    <DropdownMenuItem onSelect={onSelect} disabled={pending} className="cursor-pointer">
      <LogOut className="mr-2 h-4 w-4" />
      <span>{pending ? 'Çıkış yapılıyor…' : 'Çıkış Yap'}</span>
    </DropdownMenuItem>
  );
}
