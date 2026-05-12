/**
 * Validates a `next` redirect target so an attacker (or a stale link) cannot
 * push the user to an external host or back to the auth pages they came
 * from. Accepts only paths that:
 *
 *   - start with a single forward slash (no `//host` protocol-relative URLs)
 *   - are not the auth entry points themselves (would create a redirect loop)
 *
 * Used by both the middleware redirect and the login form's post-submit
 * navigation; keeping the rule in one place avoids the two diverging.
 */
const BLOCKED_NEXT_PREFIXES = ['/login', '/register'] as const;

export function sanitiseNextPath(value: string | null | undefined): string | null {
  if (!value) return null;
  if (!value.startsWith('/') || value.startsWith('//')) return null;
  for (const blocked of BLOCKED_NEXT_PREFIXES) {
    if (value === blocked || value.startsWith(`${blocked}/`) || value.startsWith(`${blocked}?`)) {
      return null;
    }
  }
  return value;
}
