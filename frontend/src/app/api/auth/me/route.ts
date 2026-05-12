import { bffSuccess } from '@/server/bff/response';
import { withAuth } from '@/server/bff/route-helpers';

export const runtime = 'nodejs';

export const GET = withAuth(async (_request, { user }) => {
  return bffSuccess(user);
});
