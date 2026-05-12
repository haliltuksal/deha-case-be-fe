import 'server-only';
import { AsyncLocalStorage } from 'node:async_hooks';

interface RequestContext {
  requestId: string;
}

const storage = new AsyncLocalStorage<RequestContext>();

export function runInRequestContext<T>(context: RequestContext, fn: () => Promise<T>): Promise<T> {
  return storage.run(context, fn);
}

export function getRequestContext(): RequestContext | undefined {
  return storage.getStore();
}
