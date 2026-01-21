import { formatCurrency } from '@/lib/utils';
import { ExpenseStats } from '@/types';

interface CreditCardMonthlyStatsProps {
  expenseStats: ExpenseStats;
}

export default function CreditCardMonthlyStats({
  expenseStats,
}: CreditCardMonthlyStatsProps) {
  const {
    currentMonthTotalCreditCardExpense,
    previousMonthTotalCreditCardExpense,
  } = expenseStats;

  // Determine color: red if spending increased, green if spending decreased
  const textColor =
    previousMonthTotalCreditCardExpense < currentMonthTotalCreditCardExpense
      ? 'text-red-600 dark:text-red-400'
      : previousMonthTotalCreditCardExpense > currentMonthTotalCreditCardExpense
        ? 'text-green-600 dark:text-green-400'
        : '';

  return (
    <div className="flex h-full flex-col items-center justify-center p-6">
      <div className={`text-4xl font-bold ${textColor}`}>
        {formatCurrency(currentMonthTotalCreditCardExpense)}
      </div>
      <div className="mt-2 text-sm text-muted-foreground">
        Last month: {formatCurrency(previousMonthTotalCreditCardExpense)}
      </div>
    </div>
  );
}
