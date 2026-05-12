import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';
import { DEFAULT_AUTH_COOKIE_NAME } from '@/lib/auth-constants';
import { sanitiseNextPath } from '@/lib/utils/sanitise-next';

const COOKIE_NAME = process.env.COOKIE_NAME ?? DEFAULT_AUTH_COOKIE_NAME;

const PROTECTED_PREFIXES = ['/cart', '/checkout', '/orders', '/admin'];
const PUBLIC_AUTH_PATHS = new Set(['/login', '/register']);

function isProtected(pathname: string): boolean {
  return PROTECTED_PREFIXES.some(
    (prefix) => pathname === prefix || pathname.startsWith(`${prefix}/`),
  );
}

export function middleware(request: NextRequest) {
  const { pathname, searchParams } = request.nextUrl;
  const hasToken = request.cookies.has(COOKIE_NAME);

  if (!hasToken && isProtected(pathname)) {
    const redirect = request.nextUrl.clone();
    redirect.pathname = '/login';
    redirect.search = '';
    redirect.searchParams.set('next', pathname);
    return NextResponse.redirect(redirect);
  }

  if (hasToken && PUBLIC_AUTH_PATHS.has(pathname)) {
    const redirect = request.nextUrl.clone();
    redirect.pathname = sanitiseNextPath(searchParams.get('next')) ?? '/';
    redirect.search = '';
    return NextResponse.redirect(redirect);
  }

  return NextResponse.next();
}

export const config = {
  matcher: [
    '/cart/:path*',
    '/checkout/:path*',
    '/orders/:path*',
    '/admin/:path*',
    '/login',
    '/register',
  ],
};
