import { Head } from '@inertiajs/react';
import Heading from '../../components/heading';
import TransactionForm from '../../forms/transaction-form';
import AppLayout from '../../layouts/app-layout';
import { index } from '../../routes/incomes';
import {
  AccountDropdown,
  BreadcrumbItem,
  CategoryDropdown,
  Transaction,
} from '../../types';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Incomes',
    href: index().url,
  },
  {
    title: 'Income Details',
    href: '#',
  },
];

interface IncomesShowProps {
  accounts: AccountDropdown[];
  categories: CategoryDropdown[];
  transaction: Transaction;
}

export default function TransactionsShowPage({
  accounts,
  categories,
  transaction,
}: IncomesShowProps) {
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Income Details" />

      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <Heading
          title="Income Details"
          description="View the details of an income"
        />

        <div className="w-full lg:grid lg:grid-cols-4">
          <div className="lg:col-span-2">
            <div className="flex flex-col gap-4">
              <TransactionForm
                accounts={accounts}
                categories={categories}
                transaction={transaction}
                type="income"
              />
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
