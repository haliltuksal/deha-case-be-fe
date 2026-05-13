import type { FieldValues, Path, UseFormReturn } from 'react-hook-form';
import { toast } from 'sonner';
import { isApiError } from '@/lib/errors/api-error';
import { getErrorMessage } from '@/lib/errors/error-messages';

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
