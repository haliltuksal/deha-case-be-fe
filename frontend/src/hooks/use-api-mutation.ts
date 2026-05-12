'use client';

import { useTransition } from 'react';
import { useRouter } from 'next/navigation';
import { toast } from 'sonner';
import { isApiError, type ApiError } from '@/lib/errors/api-error';
import { getErrorMessage } from '@/lib/errors/error-messages';

export interface UseApiMutationOptions<T> {
  successMessage?: string | ((result: T) => string);
  errorFallback?: string;
  /** Defaults to true. */
  refreshOnSuccess?: boolean;
  navigateOnSuccess?: string | ((result: T) => string);
  /** Defaults to `push`. */
  navigateMethod?: 'push' | 'replace';
  onSuccess?: (result: T) => void;
  /** Return `true` to suppress the default `toast.error` mapping. */
  onApiError?: (error: ApiError) => boolean | void;
  /** Skip the toast on any error. The action's own catch still runs. */
  silentError?: boolean;
}

export function useApiMutation<T = void>(options: UseApiMutationOptions<T> = {}) {
  const router = useRouter();
  const [pending, startTransition] = useTransition();

  const mutate = (action: () => Promise<T>) => {
    startTransition(async () => {
      try {
        const result = await action();

        if (options.successMessage !== undefined) {
          const msg =
            typeof options.successMessage === 'function'
              ? options.successMessage(result)
              : options.successMessage;
          toast.success(msg);
        }

        options.onSuccess?.(result);

        if (options.navigateOnSuccess !== undefined) {
          const target =
            typeof options.navigateOnSuccess === 'function'
              ? options.navigateOnSuccess(result)
              : options.navigateOnSuccess;
          if (options.navigateMethod === 'replace') router.replace(target);
          else router.push(target);
        }

        if (options.refreshOnSuccess !== false) {
          router.refresh();
        }
      } catch (error) {
        if (options.silentError) return;

        if (isApiError(error)) {
          if (options.onApiError?.(error) === true) return;
          toast.error(getErrorMessage(error.code, error.message));
        } else {
          toast.error(options.errorFallback ?? 'İşlem tamamlanamadı.');
        }
      }
    });
  };

  return { mutate, pending };
}
