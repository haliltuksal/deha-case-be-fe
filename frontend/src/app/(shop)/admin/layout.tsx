import type { ReactNode } from 'react';
import { redirect } from 'next/navigation';
import { getCurrentUser } from '@/server/auth/session';

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
