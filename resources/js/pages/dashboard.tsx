import CreditCardMonthlyStats from '@/components/credit-card-monthly-stats';
import ExpenseMonthlyStats from '@/components/expense-monthly-stats';
import { Button } from '@/components/ui/button';
import { ButtonGroup } from '@/components/ui/button-group';
import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { create as createFuelEntry } from '@/routes/fuel-entry';
import { create as createTransaction } from '@/routes/transactions';
import { index as indexVehicles } from '@/routes/vehicles';
import {
  Account,
  BillInstance,
  ExpenseStats,
  PaginateData,
  Transaction,
  type BreadcrumbItem,
} from '@/types';
import { Head, Link } from '@inertiajs/react';
import AccountsTable from '../tables/accounts-table';
import BillInstanceTable from '../tables/bill-instance-table';
import TransactionsTable from '../tables/transactions-table';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: dashboard().url,
  },
];

interface DashboardProps {
  transactions: PaginateData<Transaction>;
  accounts: PaginateData<Account>;
  pendingBills: BillInstance[];
  expenseStats: ExpenseStats;
}

export default function Dashboard({
  transactions,
  accounts,
  pendingBills,
  expenseStats,
}: DashboardProps) {
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Dashboard" />
      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div className="grid auto-rows-min gap-4 md:grid-cols-4">
          <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
            <ExpenseMonthlyStats expenseStats={expenseStats} />
          </div>
          <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
            <CreditCardMonthlyStats expenseStats={expenseStats} />
          </div>
          <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
            <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
          </div>
          <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
            <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
          </div>
        </div>

        <div className="grid auto-rows-min gap-4 md:grid-cols-4">
          <ButtonGroup aria-label="Quick actions">
            <Button variant="outline" asChild>
              <Link href={createTransaction().url}>Add expense</Link>
            </Button>
            <Button variant="outline" asChild>
              <Link href={createFuelEntry().url}>Add fuel entry</Link>
            </Button>
            <Button variant="outline" asChild>
              <Link href={indexVehicles().url}>Vehicles</Link>
            </Button>
          </ButtonGroup>
        </div>

        <div className="grid auto-rows-min gap-4 md:grid-cols-3">
          <div className="relative col-span-full overflow-hidden rounded-xl border border-sidebar-border/70 md:col-span-2 dark:border-sidebar-border">
            <TransactionsTable transactions={transactions} type="expense" />
          </div>
          <div className="relative col-span-full overflow-hidden rounded-xl border border-sidebar-border/70 md:col-span-1 dark:border-sidebar-border">
            <AccountsTable accounts={accounts} fullView={false} />
          </div>
        </div>

        <div className="grid auto-rows-min gap-4 md:grid-cols-3">
          <div className="relative col-span-2 overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
            <BillInstanceTable
              billInstances={pendingBills}
              accounts={accounts}
            />
          </div>
        </div>

        <div className="relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
          <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
        </div>
      </div>
    </AppLayout>
  );
}
