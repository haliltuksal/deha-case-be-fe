import type { FieldValues, Path, UseFormReturn } from 'react-hook-form';
import { toast } from 'sonner';
import { isApiError } from '@/lib/errors/api-error';
import { getErrorMessage } from '@/lib/errors/error-messages';

/**
 * Maps the `errors` map in an `ERR_VALIDATION` ApiError onto react-hook-form
 * field errors. Returns `true` when at least one field error was applied so
 * callers can decide whether to fall back to a global toast.
 */
export function applyApiFieldErrors<TValues extends FieldValues>(
  error: unknown,
  form: UseFormReturn<TValues>,
  fields: ReadonlyArray<Path<TValues>>,
): boolean {
  if (!isApiError(error) || error.code !== 'ERR_VALIDATION' || !error.fieldErrors) {
    return false;
  }
  let applied = false;
  for (const field of fields) {
    const messages = error.fieldErrors[field as string];
    if (messages && messages.length > 0) {
      form.setError(field, { type: 'server', message: messages[0] });
      applied = true;
    }
  }
  return applied;
}

/**
 * Standard form submission error handler: tries to apply Zod-style field
 * errors, otherwise surfaces a sonner toast with a translated message.
 */
export function handleFormSubmitError<TValues extends FieldValues>(
  error: unknown,
  form: UseFormReturn<TValues>,
  fields: ReadonlyArray<Path<TValues>>,
  fallbackMessage = 'İşlem tamamlanamadı.',
): void {
  if (applyApiFieldErrors(error, form, fields)) return;
  if (isApiError(error)) {
    toast.error(getErrorMessage(error.code, error.message));
  } else {
    toast.error(fallbackMessage);
  }
}
