import { Card, CardContent, CardFooter, CardHeader } from '@/components/ui/card';

export default function ShopLoading() {
  return (
    <main className="container mx-auto flex flex-col gap-6 px-4 py-8">
      <div className="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
        <div className="space-y-2">
          <Skeleton className="h-7 w-32" />
          <Skeleton className="h-4 w-48" />
        </div>
        <Skeleton className="h-9 w-full max-w-sm" />
      </div>
      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        {Array.from({ length: 8 }).map((_, index) => (
          <Card key={index}>
            <CardHeader>
              <Skeleton className="h-5 w-3/4" />
            </CardHeader>
            <CardContent className="space-y-3">
              <Skeleton className="h-4 w-full" />
              <Skeleton className="h-4 w-5/6" />
              <div className="flex items-baseline justify-between">
                <Skeleton className="h-7 w-24" />
                <Skeleton className="h-5 w-16" />
              </div>
            </CardContent>
            <CardFooter className="gap-2">
              <Skeleton className="h-8 flex-1" />
              <Skeleton className="h-8 flex-1" />
            </CardFooter>
          </Card>
        ))}
      </div>
    </main>
  );
}

function Skeleton({ className = '' }: { className?: string }) {
  return <div className={`animate-pulse rounded-md bg-muted ${className}`.trim()} />;
}
