import MonthlyExpenseByCategoryChart from '@/components/monthly-expense-by-category-chart';
import MonthlyExpenseChart from '@/components/monthly-expense-chart';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import type {
  BreadcrumbItem,
  MonthlyExpenseByCategoryRow,
  MonthlyExpenseRow,
} from '@/types';
import { Head, router } from '@inertiajs/react';
import { RefreshCwIcon } from 'lucide-react';

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
  monthlyExpensesByCategory: MonthlyExpenseByCategoryRow[];
}

export default function MonthlyExpenses({
  monthlyExpenses,
  monthlyExpensesByCategory,
}: MonthlyExpensesProps) {
  const refreshData = () => {
    router.visit('/reports/monthly-expenses?cacheClear=true');
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Monthly expenses" />
      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div className="flex w-full justify-end">
          <Button variant="outline" onClick={refreshData}>
            <RefreshCwIcon />
            Refresh
          </Button>
        </div>
        <div className="grid auto-rows-min gap-4 md:grid-cols-2">
          <div className="relative min-w-0 overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
            <MonthlyExpenseChart monthlyExpenses={monthlyExpenses} />
          </div>
          <div className="relative min-w-0 overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
            <MonthlyExpenseByCategoryChart
              monthlyExpensesByCategory={monthlyExpensesByCategory}
            />
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
