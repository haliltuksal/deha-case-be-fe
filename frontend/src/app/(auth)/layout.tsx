import Link from 'next/link';
import type { ReactNode } from 'react';

export default function AuthLayout({ children }: { children: ReactNode }) {
  return (
    <div className="flex min-h-screen flex-col bg-muted/30">
      <header className="border-b bg-background/80 backdrop-blur">
        <div className="container mx-auto flex h-14 items-center px-4">
          <Link href="/" className="text-base font-semibold tracking-tight">
            Dehasoft
          </Link>
        </div>
      </header>
      <main className="flex flex-1 items-center justify-center px-4 py-12">
        <div className="w-full max-w-md">{children}</div>
      </main>
    </div>
  );
}
