'use client';

import { useRouter } from 'next/navigation';
import { zodResolver } from '@hookform/resolvers/zod';
import { useForm } from 'react-hook-form';
import { toast } from 'sonner';
import { apiClient } from '@/lib/api/client';
import { handleFormSubmitError } from '@/lib/forms/map-api-errors';
import { Button } from '@/components/ui/button';
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { productInputSchema, type ProductFormValues } from '@/schemas/product';

const FIELDS = ['name', 'description', 'price', 'stock'] as const;

interface ProductFormProps {
  productId?: number;
  initialValues?: Partial<ProductFormValues>;
}

export function ProductForm({ productId, initialValues }: ProductFormProps) {
  const router = useRouter();
  const isEdit = productId !== undefined;

  const form = useForm<ProductFormValues>({
    resolver: zodResolver(productInputSchema),
    defaultValues: {
      name: initialValues?.name ?? '',
      description: initialValues?.description ?? '',
      price: initialValues?.price ?? '',
      stock: initialValues?.stock ?? 0,
    },
  });

  const onSubmit = form.handleSubmit(async (values) => {
    try {
      if (isEdit) {
        await apiClient(`/api/products/${productId}`, { method: 'PUT', json: values });
        toast.success('Ürün güncellendi.');
      } else {
        await apiClient('/api/products', { method: 'POST', json: values });
        toast.success('Ürün oluşturuldu.');
      }
      router.push('/admin/products');
      router.refresh();
    } catch (error) {
      handleFormSubmitError(error, form, FIELDS);
    }
  });

  return (
    <Form {...form}>
      <form onSubmit={onSubmit} className="space-y-4" noValidate>
        <FormField
          control={form.control}
          name="name"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Ürün Adı</FormLabel>
              <FormControl>
                <Input autoComplete="off" placeholder="Örn. Bluetooth Kulaklık" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />

        <FormField
          control={form.control}
          name="description"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Açıklama</FormLabel>
              <FormControl>
                <Textarea rows={5} placeholder="Ürün açıklaması…" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />

        <FormField
          control={form.control}
          name="price"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Fiyat (TRY)</FormLabel>
              <FormControl>
                <Input
                  inputMode="decimal"
                  pattern="\d+(\.\d{1,2})?"
                  placeholder="99.99"
                  {...field}
                />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />

        <FormField
          control={form.control}
          name="stock"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Stok</FormLabel>
              <FormControl>
                <Input
                  type="number"
                  inputMode="numeric"
                  min={0}
                  step={1}
                  {...field}
                  value={field.value}
                  onChange={(event) => field.onChange(event.target.valueAsNumber)}
                />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />

        <div className="flex items-center justify-end gap-2 pt-2">
          <Button type="button" variant="ghost" onClick={() => router.push('/admin/products')}>
            Vazgeç
          </Button>
          <Button
            type="submit"
            disabled={form.formState.isSubmitting}
            aria-busy={form.formState.isSubmitting}
          >
            {form.formState.isSubmitting ? 'Kaydediliyor…' : isEdit ? 'Güncelle' : 'Ürünü Oluştur'}
          </Button>
        </div>
      </form>
    </Form>
  );
}
