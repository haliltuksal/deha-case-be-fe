/**
 * Shared between `config/env.ts` (server) and `middleware.ts` (edge runtime).
 * Edge cannot import `server-only` modules, so we keep the default name in
 * a module that has no runtime dependencies.
 */
export const DEFAULT_AUTH_COOKIE_NAME = 'deha_token';
