import BillerExpenseReport from '@/components/biller-expense-report';
import MonthlyExpenseByCategoryChart from '@/components/monthly-expense-by-category-chart';
import MonthlyExpenseChart from '@/components/monthly-expense-chart';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import type {
  BillerExpenseDataPoint,
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
  billers: { id: number; name: string }[];
  billerExpenseData: BillerExpenseDataPoint[];
  billerExpenseBillers: { id: number; name: string }[];
  selectedBillerIds: number[];
  billerMonths: number;
}

export default function MonthlyExpenses({
  monthlyExpenses,
  monthlyExpensesByCategory,
  billers,
  billerExpenseData,
  billerExpenseBillers,
  selectedBillerIds,
  billerMonths,
}: MonthlyExpensesProps) {
  const refreshData = () => {
    router.visit('/reports/monthly-expenses?cacheClear=true');
  };

  const visitBillerReport = (
    billerMonthsValue: number,
    billerIds: number[],
  ) => {
    const search = new URLSearchParams();
    search.set('biller_months', String(billerMonthsValue));
    billerIds.forEach((id) => search.append('biller_ids[]', String(id)));
    const url =
      '/reports/monthly-expenses' +
      (search.toString() ? `?${search.toString()}` : '');
    router.visit(url, {
      preserveScroll: true,
      preserveState: false,
    });
  };

  const handleMonthsChange = (months: number) => {
    visitBillerReport(months, selectedBillerIds ?? []);
  };

  const handleBillerToggle = (id: number) => {
    const current = new Set(selectedBillerIds ?? []);
    if (current.has(id)) {
      current.delete(id);
    } else {
      current.add(id);
    }
    visitBillerReport(billerMonths ?? 3, Array.from(current));
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
        <div className="grid auto-rows-min gap-4 md:grid-cols-2">
          <BillerExpenseReport
            billers={billers}
            billerExpenseData={billerExpenseData}
            billerExpenseBillers={billerExpenseBillers}
            selectedBillerIds={selectedBillerIds}
            billerMonths={billerMonths}
            onMonthsChange={handleMonthsChange}
            onBillerToggle={handleBillerToggle}
          />
          <div className="relative min-w-0" />
        </div>
      </div>
    </AppLayout>
  );
}
