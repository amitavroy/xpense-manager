import { Head } from '@inertiajs/react';
import Heading from '../../components/heading';
import CategoryForm from '../../forms/category-form';
import AppLayout from '../../layouts/app-layout';
import { index } from '../../routes/categories';
import { BreadcrumbItem, Category } from '../../types';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Category',
    href: index().url,
  },
  {
    title: 'Add Category',
    href: '#',
  },
];

interface CategoriesShowProps {
  category: Category;
}

export default function CategoriesShowPage({ category }: CategoriesShowProps) {
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Category Details" />

      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <Heading
          title="Category Details"
          description="View the details of a category"
        />

        <div className="grid grid-cols-4">
          <div className="col-span-3 lg:col-span-2">
            <div className="flex flex-col gap-4">
              <CategoryForm category={category} />
            </div>
          </div>
          <div></div>
        </div>
      </div>
    </AppLayout>
  );
}
