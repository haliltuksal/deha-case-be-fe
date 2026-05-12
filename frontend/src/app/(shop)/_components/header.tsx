import Link from 'next/link';
import type { User } from '@/types/auth';
import type { Currency } from '@/types/currency';
import { CartIndicator } from './cart-indicator';
import { CurrencySwitcher } from './currency-switcher';
import { UserMenu } from './user-menu';

interface HeaderProps {
  user: User | null;
  currency: Currency;
  cartCount: number | null;
}

export function Header({ user, currency, cartCount }: HeaderProps) {
  return (
    <header className="sticky top-0 z-40 border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
      <div className="container mx-auto flex h-14 items-center justify-between gap-4 px-4">
        <Link href="/" className="flex items-center gap-2 text-lg font-semibold tracking-tight">
          <span className="inline-flex h-7 w-7 items-center justify-center rounded-md bg-foreground text-xs font-bold text-background">
            D
          </span>
          Dehasoft
        </Link>
        <nav className="flex items-center gap-2">
          <CurrencySwitcher value={currency} />
          {cartCount !== null && <CartIndicator count={cartCount} />}
          <UserMenu user={user} />
        </nav>
      </div>
    </header>
  );
}
