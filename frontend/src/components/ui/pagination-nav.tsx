import Link from 'next/link';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { cn } from '@/lib/utils/cn';
import type { Pagination } from '@/types/api';

interface PaginationNavProps {
  pagination: Pagination;
  /** Builds an href for the given page number. */
  buildHref: (page: number) => string;
  className?: string;
}

export function PaginationNav({ pagination, buildHref, className }: PaginationNavProps) {
  if (pagination.last_page <= 1) {
    return null;
  }

  const pages = computePageNumbers(pagination.current_page, pagination.last_page);
  const hasPrev = pagination.current_page > 1;
  const hasNext = pagination.current_page < pagination.last_page;

  return (
    <nav
      aria-label="Sayfalama"
      className={cn('mt-8 flex flex-wrap items-center justify-center gap-2', className)}
    >
      <PageLink
        href={hasPrev ? buildHref(pagination.current_page - 1) : null}
        ariaLabel="Önceki sayfa"
      >
        <ChevronLeft className="h-4 w-4" />
        <span className="hidden sm:inline">Önceki</span>
      </PageLink>

      {pages.map((page, index) =>
        page === null ? (
          <span
            key={`ellipsis-${index}`}
            className="px-2 text-sm text-muted-foreground"
            aria-hidden
          >
            …
          </span>
        ) : (
          <PageLink
            key={page}
            href={buildHref(page)}
            active={page === pagination.current_page}
            ariaLabel={`Sayfa ${page}`}
          >
            {page}
          </PageLink>
        ),
      )}

      <PageLink
        href={hasNext ? buildHref(pagination.current_page + 1) : null}
        ariaLabel="Sonraki sayfa"
      >
        <span className="hidden sm:inline">Sonraki</span>
        <ChevronRight className="h-4 w-4" />
      </PageLink>
    </nav>
  );
}

function PageLink({
  href,
  active,
  ariaLabel,
  children,
}: {
  href: string | null;
  active?: boolean;
  ariaLabel: string;
  children: React.ReactNode;
}) {
  const className = cn(
    'inline-flex h-9 min-w-[2.25rem] items-center justify-center gap-1 rounded-md border px-3 text-sm transition-colors',
    active
      ? 'border-foreground bg-foreground text-background'
      : 'border-border bg-background hover:bg-accent hover:text-accent-foreground',
    !href && 'pointer-events-none cursor-not-allowed opacity-50',
  );

  if (!href) {
    return (
      <span className={className} aria-disabled="true" aria-label={ariaLabel}>
        {children}
      </span>
    );
  }

  return (
    <Link
      href={href}
      aria-label={ariaLabel}
      aria-current={active ? 'page' : undefined}
      className={className}
    >
      {children}
    </Link>
  );
}

/**
 * Returns the visible page numbers with `null` standing for an ellipsis.
 * Always includes the first page, the last page, and a window around the
 * current page so the user keeps a stable mental model on long catalogs.
 */
function computePageNumbers(current: number, last: number): Array<number | null> {
  const visible = new Set<number>([1, last]);
  for (let p = Math.max(2, current - 1); p <= Math.min(last - 1, current + 1); p++) {
    visible.add(p);
  }

  const sorted = [...visible].sort((a, b) => a - b);
  const result: Array<number | null> = [];
  for (let i = 0; i < sorted.length; i++) {
    const previous = sorted[i - 1];
    const value = sorted[i]!;
    if (i > 0 && previous !== undefined && value - previous > 1) {
      result.push(null);
    }
    result.push(value);
  }
  return result;
}
