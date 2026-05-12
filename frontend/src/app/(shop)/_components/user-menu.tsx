'use client';

import Link from 'next/link';
import {
  ChevronDown,
  ReceiptText,
  ShieldCheck,
  ShoppingCart,
  User as UserIcon,
} from 'lucide-react';
import type { User } from '@/types/auth';
import { Button } from '@/components/ui/button';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { LogoutButton } from './logout-button';

interface UserMenuProps {
  user: User | null;
}

export function UserMenu({ user }: UserMenuProps) {
  if (!user) {
    return (
      <div className="flex items-center gap-2">
        <Button asChild variant="ghost" size="sm">
          <Link href="/login">Giriş Yap</Link>
        </Button>
        <Button asChild size="sm">
          <Link href="/register">Hesap Oluştur</Link>
        </Button>
      </div>
    );
  }

  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button variant="ghost" size="sm" className="gap-2">
          <UserIcon className="h-4 w-4" />
          <span className="max-w-[10rem] truncate">{user.name}</span>
          <ChevronDown className="h-4 w-4 opacity-60" />
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end" className="w-56">
        <DropdownMenuLabel className="flex flex-col">
          <span className="truncate text-sm font-medium">{user.name}</span>
          <span className="truncate text-xs font-normal text-muted-foreground">{user.email}</span>
        </DropdownMenuLabel>
        <DropdownMenuSeparator />
        <DropdownMenuItem asChild className="cursor-pointer">
          <Link href="/orders">
            <ReceiptText className="mr-2 h-4 w-4" />
            Siparişlerim
          </Link>
        </DropdownMenuItem>
        {user.is_admin && (
          <DropdownMenuItem asChild className="cursor-pointer">
            <Link href="/admin/products">
              <ShieldCheck className="mr-2 h-4 w-4" />
              Admin Paneli
            </Link>
          </DropdownMenuItem>
        )}
        <DropdownMenuItem asChild className="cursor-pointer">
          <Link href="/cart">
            <ShoppingCart className="mr-2 h-4 w-4" />
            Sepetim
          </Link>
        </DropdownMenuItem>
        <DropdownMenuSeparator />
        <LogoutButton />
      </DropdownMenuContent>
    </DropdownMenu>
  );
}
