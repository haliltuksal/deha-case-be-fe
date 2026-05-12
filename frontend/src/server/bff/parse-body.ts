import 'server-only';
import type { ZodError, ZodSchema } from 'zod';
import { HttpError } from '@/server/http/http-error';

/**
 * Parses and Zod-validates a request body. Failures are reported with the
 * canonical `ERR_VALIDATION` envelope so the BFF response matches what the
 * Laravel backend would have produced. This mirrors the backend's defense
 * in depth: even if a client bypasses the form, the server still rejects
 * malformed input.
 */
export async function parseJsonBody<T>(request: Request, schema: ZodSchema<T>): Promise<T> {
  let raw: unknown;
  try {
    raw = await request.json();
  } catch {
    throw new HttpError(400, {
      status: 'error',
      message: 'Geçersiz JSON gövdesi.',
      data: null,
      code: 'ERR_VALIDATION',
    });
  }

  const result = schema.safeParse(raw);
  if (!result.success) {
    throw new HttpError(422, {
      status: 'error',
      message: 'Gönderilen bilgiler geçersiz.',
      data: null,
      code: 'ERR_VALIDATION',
      errors: flattenZodErrors(result.error),
    });
  }
  return result.data;
}

function flattenZodErrors(error: ZodError): Record<string, string[]> {
  const fields: Record<string, string[]> = {};
  for (const issue of error.issues) {
    const key = issue.path.join('.') || '_';
    const bucket = fields[key] ?? (fields[key] = []);
    bucket.push(issue.message);
  }
  return fields;
}
