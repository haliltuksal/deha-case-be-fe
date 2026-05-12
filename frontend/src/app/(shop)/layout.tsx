import type { ReactNode } from 'react';
import { Header } from './_components/header';
import { readAuthCookie } from '@/server/auth/cookie';
import { getCurrentUser } from '@/server/auth/session';
import { getActiveCurrency } from '@/server/preferences/currency';
import { cartRepository } from '@/server/repositories/cart-repository';

export default async function ShopLayout({ children }: { children: ReactNode }) {
  const [user, currency, token] = await Promise.all([
    getCurrentUser(),
    getActiveCurrency(),
    readAuthCookie(),
  ]);

  const cartCount = user && token ? await safeGetCartCount(token) : null;

  return (
    <div className="flex min-h-screen flex-col">
      <Header user={user} currency={currency} cartCount={cartCount} />
      <div className="flex-1">{children}</div>
    </div>
  );
}

async function safeGetCartCount(token: string): Promise<number> {
  try {
    const cart = await cartRepository.show(token);
    return cart.item_count;
  } catch {
    return 0;
  }
}
