import 'server-only';
import { cache } from 'react';
import type { User } from '@/types/auth';
import { isHttpError } from '@/server/http/http-error';
import { authRepository } from '@/server/repositories/auth-repository';
import { readAuthCookie } from './cookie';

export const getCurrentUser = cache(async (): Promise<User | null> => {
  const token = await readAuthCookie();
  if (!token) {
    return null;
  }

  try {
    return await authRepository.me(token);
  } catch (error) {
    if (isHttpError(error) && error.status === 401) {
      return null;
    }
    throw error;
  }
});
