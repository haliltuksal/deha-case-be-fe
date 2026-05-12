/**
 * Parses a positive integer from an unknown string, returning `null` when
 * the input is missing, malformed, or non-positive. Used by both pages
 * (page=...) and BFF route handlers (per_page=...).
 */
export function parsePositiveInt(value: string | null | undefined): number | null {
  if (!value) return null;
  const parsed = Number.parseInt(value, 10);
  return Number.isInteger(parsed) && parsed > 0 ? parsed : null;
}

/**
 * Parses a page query parameter with `1` as the safe default when the value
 * is missing, malformed, or non-positive.
 */
export function parsePage(value: string | null | undefined): number {
  return parsePositiveInt(value) ?? 1;
}
