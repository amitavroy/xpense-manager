import {
  ChartContainer,
  ChartLegend,
  ChartLegendContent,
  ChartTooltip,
  type ChartConfig,
} from '@/components/ui/chart';
import { formatCurrency, formatCurrencyShort } from '@/lib/utils';
import type { MonthlyExpenseRow } from '@/types';
import { Bar, BarChart, CartesianGrid, XAxis, YAxis } from 'recharts';

function formatMonthLabel(monthKey: string): string {
  const [year, month] = monthKey.split('-').map(Number);
  const date = new Date(year, month - 1);
  const shortMonth = date.toLocaleDateString('en-US', { month: 'short' });
  const shortYear = String(year).slice(-2);
  return `${shortMonth} '${shortYear}`;
}

const chartConfig = {
  month: { label: 'Month' },
  normal: {
    label: 'Normal',
    color: 'var(--chart-1)',
  },
  credit_card: {
    label: 'Credit card',
    color: 'var(--chart-2)',
  },
  total: {
    label: 'Total',
    color: 'var(--chart-3)',
  },
} satisfies ChartConfig;

interface MonthlyExpenseChartProps {
  monthlyExpenses: MonthlyExpenseRow[];
}

export default function MonthlyExpenseChart({
  monthlyExpenses,
}: MonthlyExpenseChartProps) {
  const hasData =
    monthlyExpenses.length > 0 && monthlyExpenses.some((row) => row.total > 0);

  if (!hasData) {
    return (
      <div
        className="flex flex-col items-center justify-center gap-2 p-8 text-center text-muted-foreground"
        aria-label="No expense data for this period"
      >
        <p className="text-sm">No expense data for this period.</p>
      </div>
    );
  }

  const chartData = monthlyExpenses.map((row) => ({
    ...row,
    monthLabel: formatMonthLabel(row.month),
  }));

  return (
    <ChartContainer
      config={chartConfig}
      className="h-[300px] w-full min-w-0 overflow-hidden"
    >
      <BarChart
        data={chartData}
        margin={{ top: 8, right: 16, left: 56, bottom: 8 }}
        accessibilityLayer
      >
        <CartesianGrid strokeDasharray="3 3" vertical={false} />
        <XAxis
          dataKey="monthLabel"
          tickLine={false}
          axisLine={false}
          tickMargin={8}
        />
        <YAxis
          width={52}
          tickLine={false}
          axisLine={false}
          tickMargin={8}
          tickFormatter={(value) => formatCurrencyShort(value)}
        />
        <ChartTooltip
          content={({ active, payload }) => {
            if (!active || !payload?.length) return null;
            const data = payload[0].payload as MonthlyExpenseRow & {
              monthLabel: string;
            };
            return (
              <div className="grid min-w-32 items-start gap-1.5 rounded-lg border border-border/50 bg-background px-2.5 py-1.5 text-xs shadow-xl">
                <div className="font-medium">{data.monthLabel}</div>
                <div className="grid gap-1">
                  <div className="flex justify-between gap-4">
                    <span className="text-muted-foreground">Normal</span>
                    <span className="font-mono tabular-nums">
                      {formatCurrency(data.normal)}
                    </span>
                  </div>
                  <div className="flex justify-between gap-4">
                    <span className="text-muted-foreground">Credit card</span>
                    <span className="font-mono tabular-nums">
                      {formatCurrency(data.credit_card)}
                    </span>
                  </div>
                  <div className="flex justify-between gap-4 border-t border-border/50 pt-1 font-medium">
                    <span>Total</span>
                    <span className="font-mono tabular-nums">
                      {formatCurrency(data.total)}
                    </span>
                  </div>
                </div>
              </div>
            );
          }}
        />
        <ChartLegend content={<ChartLegendContent nameKey="dataKey" />} />
        <Bar
          dataKey="normal"
          stackId="expense"
          fill="var(--chart-1)"
          radius={[0, 0, 0, 0]}
        />
        <Bar
          dataKey="credit_card"
          stackId="expense"
          fill="var(--chart-2)"
          radius={[0, 0, 0, 0]}
        />
      </BarChart>
    </ChartContainer>
  );
}
