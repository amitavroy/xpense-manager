import { Head } from '@inertiajs/react';
import Heading from '../../components/heading';
import TransactionForm from '../../forms/transaction-form';
import AppLayout from '../../layouts/app-layout';
import { index } from '../../routes/transactions';
import {
  AccountDropdown,
  BreadcrumbItem,
  CategoryDropdown,
  Transaction,
} from '../../types';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Transactions',
    href: index().url,
  },
  {
    title: 'Add Transaction',
    href: '#',
  },
];

interface TransactionsCreateProps {
  accounts: AccountDropdown[];
  categories: CategoryDropdown[];
  transaction: Transaction;
}

export default function TransactionsCreatePage({
  accounts,
  categories,
  transaction,
}: TransactionsCreateProps) {
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Add Transaction" />

      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <Heading title="Add Transaction" description="Add a new transaction" />

        <div className="w-full lg:grid lg:grid-cols-4">
          <div className="lg:col-span-2">
            <div className="flex flex-col gap-4">
              <TransactionForm
                accounts={accounts}
                categories={categories}
                transaction={transaction}
                type="expense"
              />
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
