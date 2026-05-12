import type { ReactNode } from 'react';
import { redirect } from 'next/navigation';
import { getCurrentUser } from '@/server/auth/session';

/**
 * Admin section guard. The middleware already enforces cookie presence on
 * `/admin/*`; this layout adds the role check by hitting `/auth/me` once
 * (cached for the render). Non-admin users are silently redirected to the
 * storefront so an enumeration attempt does not even render an empty shell.
 */
export default async function AdminLayout({ children }: { children: ReactNode }) {
  const user = await getCurrentUser();
  if (!user) {
    redirect('/login?next=/admin/products');
  }
  if (!user.is_admin) {
    redirect('/');
  }
  return children;
}
