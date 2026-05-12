import type { Metadata } from 'next';
import Link from 'next/link';
import { redirect } from 'next/navigation';
import { ChevronLeft } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { CheckoutSummary } from './_components/checkout-summary';
import { PlaceOrderButton } from './_components/place-order-button';
import { readAuthCookie } from '@/server/auth/cookie';
import { getActiveCurrency } from '@/server/preferences/currency';
import { cartRepository } from '@/server/repositories/cart-repository';

export const metadata: Metadata = { title: 'Siparişi Onayla' };
export const dynamic = 'force-dynamic';

export default async function CheckoutPage() {
  const token = await readAuthCookie();
  if (!token) {
    redirect('/login?next=/checkout');
  }

  const [cart, currency] = await Promise.all([cartRepository.show(token), getActiveCurrency()]);

  if (cart.items.length === 0) {
    redirect('/cart');
  }

  return (
    <main className="container mx-auto max-w-2xl px-4 py-8">
      <Button asChild variant="ghost" size="sm" className="mb-4">
        <Link href="/cart">
          <ChevronLeft className="mr-1 h-4 w-4" /> Sepete dön
        </Link>
      </Button>

      <header className="mb-6 space-y-1">
        <h1 className="text-2xl font-semibold tracking-tight">Siparişi Onayla</h1>
        <p className="text-sm text-muted-foreground">
          Sipariş tamamlandığında sepetiniz boşaltılacak ve sipariş &ldquo;Beklemede&rdquo;
          durumunda oluşturulacak.
        </p>
      </header>

      <div className="space-y-6">
        <CheckoutSummary cart={cart} currency={currency} />
        <PlaceOrderButton />
      </div>
    </main>
  );
}
