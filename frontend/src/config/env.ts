import 'server-only';
import { z } from 'zod';
import { DEFAULT_AUTH_COOKIE_NAME } from '@/lib/auth-constants';

const envSchema = z.object({
  NODE_ENV: z.enum(['development', 'test', 'production']).default('development'),
  LARAVEL_API_URL: z
    .string()
    .url('LARAVEL_API_URL must be a valid URL (e.g. http://localhost:8080)'),
  COOKIE_NAME: z.string().min(1).default(DEFAULT_AUTH_COOKIE_NAME),
  COOKIE_DOMAIN: z.string().optional(),
  REQUEST_TIMEOUT_MS: z.coerce.number().int().positive().default(15000),
});

export type AppEnv = z.infer<typeof envSchema>;

let cachedEnv: AppEnv | null = null;

function loadEnv(): AppEnv {
  if (cachedEnv) {
    return cachedEnv;
  }

  const parsed = envSchema.safeParse(process.env);
  if (!parsed.success) {
    const issues = parsed.error.issues
      .map((issue) => `  - ${issue.path.join('.') || '(root)'}: ${issue.message}`)
      .join('\n');
    throw new Error(`Invalid server environment variables:\n${issues}`);
  }
  cachedEnv = parsed.data;
  return cachedEnv;
}

export const env = new Proxy({} as AppEnv, {
  get(_target, property) {
    const loaded = loadEnv();
    return loaded[property as keyof AppEnv];
  },
}) satisfies AppEnv;
