import 'server-only';
import type { User } from '@/types/auth';
import { HttpError } from '@/server/http/http-error';
import { getCurrentUser } from './session';

export async function requireAuth(): Promise<User> {
  const user = await getCurrentUser();
  if (!user) {
    throw HttpError.unauthenticated();
  }
  return user;
}

export async function requireAdmin(): Promise<User> {
  const user = await requireAuth();
  if (!user.is_admin) {
    throw HttpError.forbidden();
  }
  return user;
}
