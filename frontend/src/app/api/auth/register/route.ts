import { setAuthCookie } from '@/server/auth/cookie';
import { parseJsonBody } from '@/server/bff/parse-body';
import { bffSuccess } from '@/server/bff/response';
import { withErrorHandling } from '@/server/bff/route-helpers';
import { authRepository } from '@/server/repositories/auth-repository';
import { registerSchema } from '@/schemas/auth';

export const runtime = 'nodejs';

export const POST = withErrorHandling(async (request) => {
  const input = await parseJsonBody(request, registerSchema);
  const session = await authRepository.register(input);
  await setAuthCookie(session.access_token, { expiresInSeconds: session.expires_in });
  return bffSuccess({ user: session.user }, { status: 201, message: 'Hesap oluşturuldu.' });
});
