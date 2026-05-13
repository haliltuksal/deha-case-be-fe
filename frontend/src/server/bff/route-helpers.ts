import 'server-only';
import type { NextRequest, NextResponse } from 'next/server';
import type { User } from '@/types/auth';
import { readAuthCookie } from '@/server/auth/cookie';
import { requireAdmin, requireAuth } from '@/server/auth/guard';
import { HttpError } from '@/server/http/http-error';
import { runInRequestContext } from '@/server/http/request-context';
import { getOrCreateRequestId } from '@/server/http/request-id';
import { toNextResponse } from './handle-error';

interface RouteContext<TParams> {
  params: Promise<TParams>;
}

export type RouteHandler<TParams> = (
  request: NextRequest,
  context: RouteContext<TParams>,
) => Promise<NextResponse> | NextResponse;

export type AuthenticatedRouteHandler<TParams> = (
  request: NextRequest,
  context: RouteContext<TParams> & { user: User },
) => Promise<NextResponse> | NextResponse;

function withRequestContext(
  request: NextRequest,
  fn: () => Promise<NextResponse>,
): Promise<NextResponse> {
  const requestId = getOrCreateRequestId(request.headers);
  return runInRequestContext({ requestId }, fn);
}

export function withErrorHandling<TParams = Record<string, string>>(
  handler: RouteHandler<TParams>,
): RouteHandler<TParams> {
  return (request, context) =>
    withRequestContext(request, async () => {
      try {
        return await handler(request, context);
      } catch (error) {
        return toNextResponse(error);
      }
    });
}

export function withAuth<TParams = Record<string, string>>(
  handler: AuthenticatedRouteHandler<TParams>,
): RouteHandler<TParams> {
  return (request, context) =>
    withRequestContext(request, async () => {
      try {
        const user = await requireAuth();
        return await handler(request, { ...context, user });
      } catch (error) {
        return toNextResponse(error);
      }
    });
}

export type TokenRouteHandler<TParams> = (
  request: NextRequest,
  context: RouteContext<TParams> & { token: string },
) => Promise<NextResponse> | NextResponse;

export function withToken<TParams = Record<string, string>>(
  handler: TokenRouteHandler<TParams>,
): RouteHandler<TParams> {
  return (request, context) =>
    withRequestContext(request, async () => {
      try {
        const token = await readAuthCookie();
        if (!token) {
          throw HttpError.unauthenticated();
        }
        return await handler(request, { ...context, token });
      } catch (error) {
        return toNextResponse(error);
      }
    });
}

export type AdminTokenRouteHandler<TParams> = (
  request: NextRequest,
  context: RouteContext<TParams> & { user: User; token: string },
) => Promise<NextResponse> | NextResponse;

export function withAdminToken<TParams = Record<string, string>>(
  handler: AdminTokenRouteHandler<TParams>,
): RouteHandler<TParams> {
  return (request, context) =>
    withRequestContext(request, async () => {
      try {
        const user = await requireAdmin();
        const token = await readAuthCookie();
        if (!token) {
          throw HttpError.unauthenticated();
        }
        return await handler(request, { ...context, user, token });
      } catch (error) {
        return toNextResponse(error);
      }
    });
}
