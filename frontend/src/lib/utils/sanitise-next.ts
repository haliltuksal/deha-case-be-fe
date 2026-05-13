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
