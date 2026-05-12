import { describe, expect, it } from 'vitest';
import { render, screen } from '@testing-library/react';
import { OrderStatusBadge } from './order-status-badge';

describe('OrderStatusBadge', () => {
  it('renders the Turkish label for pending orders', () => {
    render(<OrderStatusBadge status="pending" />);
    expect(screen.getByText('Beklemede')).toBeInTheDocument();
  });

  it('renders the Turkish label for completed orders', () => {
    render(<OrderStatusBadge status="completed" />);
    expect(screen.getByText('Tamamlandı')).toBeInTheDocument();
  });

  it('renders the Turkish label for cancelled orders', () => {
    render(<OrderStatusBadge status="cancelled" />);
    expect(screen.getByText('İptal Edildi')).toBeInTheDocument();
  });
});
