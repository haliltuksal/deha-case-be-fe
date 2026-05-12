import { NextResponse } from 'next/server';
import { withErrorHandling } from '@/server/bff/route-helpers';
import { laravel } from '@/server/http/laravel-client';

export const runtime = 'nodejs';

interface BackendHealth {
  status: 'success' | 'error';
  message: string | null;
  data: {
    status: 'ok' | 'degraded';
    timestamp: string;
    services: Record<string, 'ok' | 'down'>;
  };
}

/**
 * BFF health probe. Surfaces the backend's health envelope so a single
 * `curl /api/health` against the frontend tells you whether the storefront
 * can reach Laravel and its dependencies.
 */
export const GET = withErrorHandling(async () => {
  const upstream = await laravel<BackendHealth>('/api/v1/health');
  const overall = upstream.data.status;
  const httpStatus = overall === 'ok' ? 200 : 503;
  return NextResponse.json(
    {
      status: overall === 'ok' ? 'success' : 'error',
      message: overall === 'ok' ? null : 'Backend services degraded.',
      data: {
        overall,
        timestamp: upstream.data.timestamp,
        bff: 'ok' as const,
        backend: upstream.data.services,
      },
    },
    { status: httpStatus },
  );
});
