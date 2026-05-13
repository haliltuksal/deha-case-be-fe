export function parsePositiveInt(value: string | null | undefined): number | null {
  if (!value) return null;
  const parsed = Number.parseInt(value, 10);
  return Number.isInteger(parsed) && parsed > 0 ? parsed : null;
}

export function parsePage(value: string | null | undefined): number {
  return parsePositiveInt(value) ?? 1;
}
