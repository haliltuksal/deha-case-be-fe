import type { Metadata } from 'next';
import Link from 'next/link';
import { ChevronLeft } from 'lucide-react';
import { ProductForm } from '../_components/product-form';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

export const metadata: Metadata = { title: 'Yeni Ürün' };

export default function NewProductPage() {
  return (
    <main className="container mx-auto max-w-2xl px-4 py-8">
      <Button asChild variant="ghost" size="sm" className="mb-4">
        <Link href="/admin/products">
          <ChevronLeft className="mr-1 h-4 w-4" /> Ürünlere dön
        </Link>
      </Button>

      <Card>
        <CardHeader>
          <CardTitle>Yeni Ürün</CardTitle>
        </CardHeader>
        <CardContent>
          <ProductForm />
        </CardContent>
      </Card>
    </main>
  );
}
