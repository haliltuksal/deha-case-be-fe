import { NextResponse } from 'next/server';
import { clearAuthCookie, readAuthCookie } from '@/server/auth/cookie';
import { withErrorHandling } from '@/server/bff/route-helpers';
import { authRepository } from '@/server/repositories/auth-repository';

export const runtime = 'nodejs';

export const POST = withErrorHandling(async () => {
  const token = await readAuthCookie();

  if (token) {
    try {
      await authRepository.logout(token);
    } catch (error) {
      console.warn('[bff] backend logout failed; clearing cookie anyway', error);
    }
  }

  await clearAuthCookie();
  return new NextResponse(null, { status: 204 });
});
