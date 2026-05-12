import { Loader2 } from 'lucide-react';

export default function AuthLoading() {
  return (
    <div
      role="status"
      aria-label="Yükleniyor"
      className="flex min-h-[300px] items-center justify-center"
    >
      <Loader2 className="h-6 w-6 animate-spin text-muted-foreground" aria-hidden />
    </div>
  );
}
