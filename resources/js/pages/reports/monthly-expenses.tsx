import MonthlyExpenseChart from '@/components/monthly-expense-chart';
import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import type { BreadcrumbItem, MonthlyExpenseRow } from '@/types';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: dashboard().url,
  },
  {
    title: 'Monthly expenses',
    href: '/reports/monthly-expenses',
  },
];

interface MonthlyExpensesProps {
  monthlyExpenses: MonthlyExpenseRow[];
}

export default function MonthlyExpenses({
  monthlyExpenses,
}: MonthlyExpensesProps) {
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Monthly expenses" />
      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div className="grid auto-rows-min gap-4 md:grid-cols-2">
          <div className="relative min-w-0 overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
            <MonthlyExpenseChart monthlyExpenses={monthlyExpenses} />
          </div>
          <div className="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
            <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
