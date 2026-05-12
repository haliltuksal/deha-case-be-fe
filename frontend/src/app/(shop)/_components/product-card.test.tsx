import { describe, expect, it, vi } from 'vitest';
import { render, screen } from '@testing-library/react';

vi.mock('next/navigation', () => ({
  useRouter: () => ({ push: vi.fn(), replace: vi.fn(), refresh: vi.fn() }),
  usePathname: () => '/',
}));

vi.mock('sonner', () => ({ toast: { error: vi.fn(), success: vi.fn() } }));

import { ProductCard } from './product-card';
import type { Product } from '@/types/product';

const baseProduct: Product = {
  id: 7,
  name: 'Bluetooth Kulaklık',
  description: 'Aktif gürültü engelleme ve 30 saat pil ömrü.',
  price: '299.99',
  stock: 12,
  prices: { TRY: '299.99', USD: '8.99', EUR: '7.50' },
  created_at: '2026-05-05T10:00:00.000Z',
  updated_at: '2026-05-05T10:00:00.000Z',
};

describe('ProductCard', () => {
  it('renders the product name and the price in the active currency', () => {
    render(<ProductCard product={baseProduct} currency="USD" />);
    expect(screen.getByText('Bluetooth Kulaklık')).toBeInTheDocument();
    expect(screen.getByText(/8\.99/)).toBeInTheDocument();
    expect(screen.getByText('12 adet')).toBeInTheDocument();
  });

  it('switches the displayed price when currency changes', () => {
    const { rerender } = render(<ProductCard product={baseProduct} currency="USD" />);
    expect(screen.getByText(/\$8\.99/)).toBeInTheDocument();

    rerender(<ProductCard product={baseProduct} currency="TRY" />);
    expect(screen.getByText(/299,99/)).toBeInTheDocument();
  });

  it('marks the action button as Tükendi when stock is zero', () => {
    render(<ProductCard product={{ ...baseProduct, stock: 0 }} currency="TRY" />);
    // Both the stock badge and the disabled CTA carry the Tükendi label.
    expect(screen.getAllByText('Tükendi').length).toBeGreaterThanOrEqual(2);
    expect(screen.getByRole('button', { name: /tükendi/i })).toBeDisabled();
  });
});
