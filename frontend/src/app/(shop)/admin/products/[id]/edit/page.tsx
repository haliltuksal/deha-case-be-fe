import type { Metadata } from 'next';
import Link from 'next/link';
import { notFound } from 'next/navigation';
import { ChevronLeft } from 'lucide-react';
import { ProductForm } from '../../_components/product-form';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { isHttpError } from '@/server/http/http-error';
import { productRepository } from '@/server/repositories/product-repository';
import type { Product } from '@/types/product';

export const dynamic = 'force-dynamic';

interface EditProductPageProps {
  params: Promise<{ id: string }>;
}

export async function generateMetadata({ params }: EditProductPageProps): Promise<Metadata> {
  const product = await loadProduct(await params);
  return {
    title: product ? `Düzenle: ${product.name}` : 'Ürün bulunamadı',
  };
}

export default async function EditProductPage({ params }: EditProductPageProps) {
  const product = await loadProduct(await params);
  if (!product) {
    notFound();
  }

  return (
    <main className="container mx-auto max-w-2xl px-4 py-8">
      <Button asChild variant="ghost" size="sm" className="mb-4">
        <Link href="/admin/products">
          <ChevronLeft className="mr-1 h-4 w-4" /> Ürünlere dön
        </Link>
      </Button>

      <Card>
        <CardHeader>
          <CardTitle>Ürünü Düzenle</CardTitle>
        </CardHeader>
        <CardContent>
          <ProductForm
            productId={product.id}
            initialValues={{
              name: product.name,
              description: product.description,
              price: product.price,
              stock: product.stock,
            }}
          />
        </CardContent>
      </Card>
    </main>
  );
}

async function loadProduct(params: { id: string }): Promise<Product | null> {
  const id = Number.parseInt(params.id, 10);
  if (!Number.isInteger(id) || id <= 0) {
    return null;
  }

  try {
    return await productRepository.show(id);
  } catch (error) {
    if (isHttpError(error) && error.status === 404) {
      return null;
    }
    throw error;
  }
}
