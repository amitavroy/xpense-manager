import { Head, router } from '@inertiajs/react';
import { PlusIcon } from 'lucide-react';
import Heading from '../../components/heading';
import { Button } from '../../components/ui/button';
import AppLayout from '../../layouts/app-layout';
import incomeRoutes from '../../routes/incomes';
import TransactionsTable from '../../tables/transactions-table';
import { BreadcrumbItem, PaginateData, Transaction } from '../../types';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Incomes',
    href: incomeRoutes.index().url,
  },
];

interface IncomesIndexProps {
  incomes: PaginateData<Transaction>;
}

export default function IncomesIndexPage({ incomes }: IncomesIndexProps) {
  const goToAddIncomePage = () => {
    router.visit(incomeRoutes.create().url);
  };
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Incomes" />

      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <Heading title="Incomes" description="All my incomes" />

        <div className="flex w-full justify-end">
          <Button onClick={goToAddIncomePage}>
            <PlusIcon />
            Add Income
          </Button>
        </div>

        <div className="flex flex-col gap-4">
          <TransactionsTable transactions={incomes} type="income" />
        </div>
      </div>
    </AppLayout>
  );
}
