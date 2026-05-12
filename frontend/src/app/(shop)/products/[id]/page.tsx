import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { ProductDetail } from './_components/product-detail';
import { getActiveCurrency } from '@/server/preferences/currency';
import { isHttpError } from '@/server/http/http-error';
import { productRepository } from '@/server/repositories/product-repository';
import type { Product } from '@/types/product';

export const dynamic = 'force-dynamic';

interface ProductPageProps {
  params: Promise<{ id: string }>;
}

export async function generateMetadata({ params }: ProductPageProps): Promise<Metadata> {
  const product = await loadProduct(await params);
  if (!product) {
    return { title: 'Ürün bulunamadı' };
  }
  return {
    title: product.name,
    description: product.description,
  };
}

export default async function ProductPage({ params }: ProductPageProps) {
  const product = await loadProduct(await params);
  if (!product) {
    notFound();
  }

  const currency = await getActiveCurrency();
  return <ProductDetail product={product} currency={currency} />;
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
