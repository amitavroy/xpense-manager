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
    title: 'Transaction Details',
    href: '#',
  },
];

interface TransactionsShowProps {
  accounts: AccountDropdown[];
  categories: CategoryDropdown[];
  transaction: Transaction;
}

export default function TransactionsShowPage({
  accounts,
  categories,
  transaction,
}: TransactionsShowProps) {
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Transaction Details" />

      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <Heading
          title="Transaction Details"
          description="View the details of a transaction"
        />

        <div className="grid grid-cols-4">
          <div className="col-span-3 lg:col-span-2">
            <div className="flex flex-col gap-4">
              <TransactionForm
                accounts={accounts}
                categories={categories}
                transaction={transaction}
                type="expense"
              />
            </div>
          </div>
          <div></div>
        </div>
      </div>
    </AppLayout>
  );
}
