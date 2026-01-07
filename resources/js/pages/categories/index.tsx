import { Head, router } from '@inertiajs/react';
import { PlusIcon } from 'lucide-react';
import Heading from '../../components/heading';
import { Button } from '../../components/ui/button';
import AppLayout from '../../layouts/app-layout';
import { create, index } from '../../routes/categories';
import CategoryTable from '../../tables/category-table';
import { BreadcrumbItem, Category, PaginateData } from '../../types';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Categories',
    href: index().url,
  },
];

interface CategoriesIndexProps {
  categories: PaginateData<Category>;
}

export default function CategoriesIndexPage({
  categories,
}: CategoriesIndexProps) {
  const goToAddCategoryPage = () => {
    router.visit(create().url);
  };
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Categories" />

      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <Heading
          title="Transactions"
          description="All my transactions transactions"
        />

        <div className="flex w-full justify-end">
          <Button onClick={goToAddCategoryPage}>
            <PlusIcon />
            Add Category
          </Button>
        </div>

        <div className="grid grid-cols-3">
          <div className="col-span-2">
            <div className="flex flex-col gap-4">
              <CategoryTable categories={categories} />
            </div>
          </div>
          <div></div>
        </div>
      </div>
    </AppLayout>
  );
}
