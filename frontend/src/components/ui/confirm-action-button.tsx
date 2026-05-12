'use client';

import { useState, type ReactElement } from 'react';
import { apiClient } from '@/lib/api/client';
import { useApiMutation } from '@/hooks/use-api-mutation';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog';

interface ConfirmActionButtonProps {
  trigger: ReactElement;
  title: string;
  description: string;
  cancelLabel?: string;
  actionLabel: string;
  pendingLabel: string;
  endpoint: string;
  method: 'POST' | 'PUT' | 'DELETE';
  body?: unknown;
  successMessage?: string;
  errorFallback?: string;
}

export function ConfirmActionButton({
  trigger,
  title,
  description,
  cancelLabel = 'Vazgeç',
  actionLabel,
  pendingLabel,
  endpoint,
  method,
  body,
  successMessage,
  errorFallback,
}: ConfirmActionButtonProps) {
  const [open, setOpen] = useState(false);
  const { mutate, pending } = useApiMutation<void>({
    successMessage,
    errorFallback,
    onSuccess: () => setOpen(false),
  });

  const onConfirm = () =>
    mutate(async () => {
      await apiClient(endpoint, {
        method,
        json: body,
      });
    });

  return (
    <AlertDialog open={open} onOpenChange={setOpen}>
      <AlertDialogTrigger asChild>{trigger}</AlertDialogTrigger>
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>{title}</AlertDialogTitle>
          <AlertDialogDescription>{description}</AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel disabled={pending}>{cancelLabel}</AlertDialogCancel>
          <AlertDialogAction
            onClick={(event) => {
              event.preventDefault();
              onConfirm();
            }}
            disabled={pending}
          >
            {pending ? pendingLabel : actionLabel}
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  );
}
