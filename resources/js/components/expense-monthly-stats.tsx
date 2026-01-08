import { formatCurrency } from '@/lib/utils';
import { ExpenseStats } from '@/types';

interface ExpenseMonthlyStatsProps {
  expenseStats: ExpenseStats;
}

export default function ExpenseMonthlyStats({
  expenseStats,
}: ExpenseMonthlyStatsProps) {
  const { currentMonthTotalExpense, previousMonthTotalExpense } = expenseStats;

  // Determine color: red if spending increased, green if spending decreased
  const textColor =
    previousMonthTotalExpense < currentMonthTotalExpense
      ? 'text-red-600 dark:text-red-400'
      : previousMonthTotalExpense > currentMonthTotalExpense
        ? 'text-green-600 dark:text-green-400'
        : '';

  return (
    <div className="flex h-full flex-col items-center justify-center p-6">
      <div className={`text-4xl font-bold ${textColor}`}>
        {formatCurrency(currentMonthTotalExpense)}
      </div>
      <div className="mt-2 text-sm text-muted-foreground">
        Last month: {formatCurrency(previousMonthTotalExpense)}
      </div>
    </div>
  );
}
