import { Head, router } from '@inertiajs/react';
import { PlusIcon } from 'lucide-react';
import Heading from '../../components/heading';
import { Button } from '../../components/ui/button';
import AppLayout from '../../layouts/app-layout';
import { create, index } from '../../routes/transactions';
import TransactionsTable from '../../tables/transactions-table';
import { BreadcrumbItem, PaginateData, Transaction } from '../../types';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Transactions',
    href: index().url,
  },
];

interface TransactionsIndexProps {
  transactions: PaginateData<Transaction>;
}

export default function TransactionsIndexPage({
  transactions,
}: TransactionsIndexProps) {
  const goToAddTransactionPage = () => {
    router.visit(create().url);
  };
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Transactions" />

      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <Heading
          title="Transactions"
          description="All my transactions transactions"
        />

        <div className="flex w-full justify-end">
          <Button onClick={goToAddTransactionPage}>
            <PlusIcon />
            Add Expense
          </Button>
        </div>

        <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
          <div className="col-span-full md:col-span-2">
            <div className="flex flex-col gap-4">
              <TransactionsTable transactions={transactions} type="expense" />
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
