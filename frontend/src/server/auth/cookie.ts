import 'server-only';
import { cookies } from 'next/headers';
import type { ResponseCookie } from 'next/dist/compiled/@edge-runtime/cookies';
import { env } from '@/config/env';

const SAFETY_MARGIN_SECONDS = 30;

export interface AuthCookieOptions {
  /** Token lifetime in seconds (matches Laravel `expires_in`). */
  expiresInSeconds: number;
}

function baseCookieOptions(): Pick<
  ResponseCookie,
  'httpOnly' | 'sameSite' | 'secure' | 'path' | 'domain'
> {
  return {
    httpOnly: true,
    sameSite: 'lax',
    secure: env.NODE_ENV === 'production',
    path: '/',
    ...(env.COOKIE_DOMAIN ? { domain: env.COOKIE_DOMAIN } : {}),
  };
}

/**
 * Stores the bearer token in an HttpOnly cookie. The cookie is invisible to
 * client-side JavaScript and is the only place the token lives in the browser.
 */
export async function setAuthCookie(token: string, options: AuthCookieOptions): Promise<void> {
  const store = await cookies();
  const maxAge = Math.max(60, options.expiresInSeconds - SAFETY_MARGIN_SECONDS);
  store.set({
    name: env.COOKIE_NAME,
    value: token,
    maxAge,
    ...baseCookieOptions(),
  });
}

export async function clearAuthCookie(): Promise<void> {
  const store = await cookies();
  store.set({
    name: env.COOKIE_NAME,
    value: '',
    maxAge: 0,
    ...baseCookieOptions(),
  });
}

export async function readAuthCookie(): Promise<string | null> {
  const store = await cookies();
  const value = store.get(env.COOKIE_NAME)?.value;
  return value && value.length > 0 ? value : null;
}
