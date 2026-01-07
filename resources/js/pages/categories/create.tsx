import { Head } from '@inertiajs/react';
import Heading from '../../components/heading';
import CategoryForm from '../../forms/category-form';
import AppLayout from '../../layouts/app-layout';
import { index } from '../../routes/categories';
import { BreadcrumbItem, Category } from '../../types';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Categories',
    href: index().url,
  },
  {
    title: 'Add Category',
    href: '#',
  },
];

interface CategoriesCreateProps {
  category: Category;
}

export default function CategoriesCreatePage({
  category,
}: CategoriesCreateProps) {
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Create Category" />

      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <Heading title="Add Account" description="Add a new account" />

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
