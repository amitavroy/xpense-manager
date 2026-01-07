import { Head } from '@inertiajs/react';
import Heading from '../../components/heading';
import BillerForm from '../../forms/biller-form';
import AppLayout from '../../layouts/app-layout';
import billers from '../../routes/billers';
import { Biller, BreadcrumbItem, Category } from '../../types';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Billers',
    href: billers.index().url,
  },
  {
    title: 'Create',
    href: billers.create().url,
  },
];

interface BillerCreateProps {
  biller: Biller;
  categories: Category[];
}

export default function BillersIndexPage({
  biller,
  categories,
}: BillerCreateProps) {
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Billers" />

      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <Heading title="Create Biller" description="Create a new biller" />

        <div className="grid grid-cols-4">
          <div className="col-span-2">
            <div className="flex flex-col gap-4">
              <BillerForm biller={biller} categories={categories} />
            </div>
          </div>
          <div></div>
        </div>
      </div>
    </AppLayout>
  );
}
