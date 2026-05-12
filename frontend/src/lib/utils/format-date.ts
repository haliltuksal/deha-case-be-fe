const dateTimeFormatter = new Intl.DateTimeFormat('tr-TR', {
  dateStyle: 'long',
  timeStyle: 'short',
});

const dateFormatter = new Intl.DateTimeFormat('tr-TR', {
  dateStyle: 'long',
});

/**
 * Formats an ISO date string for display in tr-TR locale, e.g.
 * "5 Mayıs 2026 12:34". Returns the original input if it cannot be parsed.
 */
export function formatDateTime(value: string): string {
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  return dateTimeFormatter.format(date);
}

export function formatDate(value: string): string {
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  return dateFormatter.format(date);
}
