import { Head } from '@inertiajs/react';
import Heading from '../../components/heading';
import IncomeForm from '../../forms/income-form';
import AppLayout from '../../layouts/app-layout';
import incomeRoutes from '../../routes/incomes';
import {
  AccountDropdown,
  BreadcrumbItem,
  CategoryDropdown,
  Transaction,
} from '../../types';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Incomes',
    href: incomeRoutes.index().url,
  },
  {
    title: 'Add Income',
    href: '#',
  },
];

interface IncomesCreateProps {
  accounts: AccountDropdown[];
  categories: CategoryDropdown[];
  transaction: Transaction;
}

export default function IncomesIndexPage({
  transaction,
  accounts,
  categories,
}: IncomesCreateProps) {
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Incomes" />

      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <Heading title="Incomes" description="All my incomes" />

        <div className="w-full lg:grid lg:grid-cols-4">
          <div className="lg:col-span-2">
            <div className="flex flex-col gap-4">
              <IncomeForm
                accounts={accounts}
                categories={categories}
                transaction={transaction}
              />
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
