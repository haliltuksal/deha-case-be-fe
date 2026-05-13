import { bffSuccess } from '@/server/bff/response';
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

export const GET = withErrorHandling(async () => {
  const upstream = await laravel<BackendHealth>('/api/v1/health');
  const overall = upstream.data.status;
  const isHealthy = overall === 'ok';

  return bffSuccess(
    {
      overall,
      timestamp: upstream.data.timestamp,
      bff: 'ok' as const,
      backend: upstream.data.services,
    },
    {
      status: isHealthy ? 200 : 503,
      message: isHealthy ? null : 'Backend services degraded.',
    },
  );
});
