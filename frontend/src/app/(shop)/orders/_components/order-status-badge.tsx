import { cn } from '@/lib/utils/cn';
import type { OrderStatus } from '@/types/order';

const STATUS_LABELS: Record<OrderStatus, string> = {
  pending: 'Beklemede',
  completed: 'Tamamlandı',
  cancelled: 'İptal Edildi',
};

const STATUS_STYLES: Record<OrderStatus, string> = {
  pending: 'bg-amber-100 text-amber-900 dark:bg-amber-500/15 dark:text-amber-200',
  completed: 'bg-emerald-100 text-emerald-900 dark:bg-emerald-500/15 dark:text-emerald-200',
  cancelled: 'bg-red-100 text-red-900 dark:bg-red-500/15 dark:text-red-200',
};

interface OrderStatusBadgeProps {
  status: OrderStatus;
  className?: string;
}

export function OrderStatusBadge({ status, className }: OrderStatusBadgeProps) {
  return (
    <span
      className={cn(
        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
        STATUS_STYLES[status],
        className,
      )}
    >
      {STATUS_LABELS[status]}
    </span>
  );
}
