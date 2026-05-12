import type { Metadata } from 'next';
import { redirect } from 'next/navigation';
import { CartItemRow } from './_components/cart-item-row';
import { CartSummary } from './_components/cart-summary';
import { ClearCartButton } from './_components/clear-cart-button';
import { EmptyCart } from './_components/empty-cart';
import { readAuthCookie } from '@/server/auth/cookie';
import { getActiveCurrency } from '@/server/preferences/currency';
import { cartRepository } from '@/server/repositories/cart-repository';

export const metadata: Metadata = {
  title: 'Sepetim',
};

export const dynamic = 'force-dynamic';

export default async function CartPage() {
  const token = await readAuthCookie();
  if (!token) {
    redirect('/login?next=/cart');
  }

  const [cart, currency] = await Promise.all([cartRepository.show(token), getActiveCurrency()]);

  if (cart.items.length === 0) {
    return (
      <main className="container mx-auto px-4 py-8">
        <h1 className="mb-6 text-2xl font-semibold tracking-tight">Sepetim</h1>
        <EmptyCart />
      </main>
    );
  }

  return (
    <main className="container mx-auto px-4 py-8">
      <div className="grid gap-8 lg:grid-cols-[1fr_360px]">
        <section className="space-y-4">
          <header className="flex items-center justify-between">
            <h1 className="text-2xl font-semibold tracking-tight">
              Sepetim{' '}
              <span className="text-base font-normal text-muted-foreground">
                ({cart.item_count} ürün)
              </span>
            </h1>
            <ClearCartButton />
          </header>
          <div className="space-y-3">
            {cart.items.map((item) => (
              <CartItemRow key={item.id} item={item} currency={currency} />
            ))}
          </div>
        </section>
        <aside>
          <CartSummary cart={cart} currency={currency} />
        </aside>
      </div>
    </main>
  );
}
