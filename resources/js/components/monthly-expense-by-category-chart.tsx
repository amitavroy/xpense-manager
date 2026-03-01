import {
  ChartContainer,
  ChartLegend,
  ChartLegendContent,
  ChartTooltip,
  type ChartConfig,
} from '@/components/ui/chart';
import { formatCurrency, formatCurrencyShort } from '@/lib/utils';
import type { MonthlyExpenseByCategoryRow } from '@/types';
import { Bar, BarChart, CartesianGrid, XAxis, YAxis } from 'recharts';

const CHART_COLORS = [
  'var(--chart-1)',
  'var(--chart-2)',
  'var(--chart-3)',
  'var(--chart-4)',
  'var(--chart-5)',
];

function formatMonthLabel(monthKey: string): string {
  const [year, month] = monthKey.split('-').map(Number);
  const date = new Date(year, month - 1);
  const shortMonth = date.toLocaleDateString('en-US', { month: 'short' });
  const shortYear = String(year).slice(-2);
  return `${shortMonth} '${shortYear}`;
}

function categoryKey(id: number): string {
  return `cat_${id}`;
}

interface MonthlyExpenseByCategoryChartProps {
  monthlyExpensesByCategory: MonthlyExpenseByCategoryRow[];
}

export default function MonthlyExpenseByCategoryChart({
  monthlyExpensesByCategory,
}: MonthlyExpenseByCategoryChartProps) {
  const uniqueCategories = (() => {
    const byId = new Map<number, { id: number; name: string }>();
    for (const row of monthlyExpensesByCategory) {
      for (const cat of row.categories) {
        if (!byId.has(cat.category_id)) {
          byId.set(cat.category_id, {
            id: cat.category_id,
            name: cat.category_name,
          });
        }
      }
    }
    return Array.from(byId.values()).sort((a, b) => a.id - b.id);
  })();

  const hasData =
    uniqueCategories.length > 0 &&
    monthlyExpensesByCategory.some(
      (row) =>
        row.categories.length > 0 && row.categories.some((c) => c.total > 0),
    );

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

  const chartConfig: ChartConfig = {
    month: { label: 'Month' },
    ...Object.fromEntries(
      uniqueCategories.map((cat, i) => [
        categoryKey(cat.id),
        {
          label: cat.name,
          color: CHART_COLORS[i % CHART_COLORS.length],
        },
      ]),
    ),
  };

  const chartData = monthlyExpensesByCategory.map((row) => {
    const categoryTotals: Record<string, number> = {};
    for (const cat of uniqueCategories) {
      const found = row.categories.find((c) => c.category_id === cat.id);
      categoryTotals[categoryKey(cat.id)] = found ? found.total : 0;
    }
    return {
      month: row.month,
      monthLabel: formatMonthLabel(row.month),
      ...categoryTotals,
    };
  });

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
            const data = payload[0].payload as Record<string, unknown> & {
              monthLabel: string;
            };
            let total = 0;
            const lines = uniqueCategories
              .filter((cat) => {
                const key = categoryKey(cat.id);
                const value = data[key] as number;
                return value != null && value > 0;
              })
              .map((cat) => {
                const key = categoryKey(cat.id);
                const value = (data[key] as number) ?? 0;
                total += value;
                return { name: cat.name, value };
              });
            return (
              <div className="grid min-w-32 items-start gap-1.5 rounded-lg border border-border/50 bg-background px-2.5 py-1.5 text-xs shadow-xl">
                <div className="font-medium">{data.monthLabel}</div>
                <div className="grid gap-1">
                  {lines.map(({ name, value }) => (
                    <div key={name} className="flex justify-between gap-4">
                      <span className="text-muted-foreground">{name}</span>
                      <span className="font-mono tabular-nums">
                        {formatCurrency(value)}
                      </span>
                    </div>
                  ))}
                  <div className="flex justify-between gap-4 border-t border-border/50 pt-1 font-medium">
                    <span>Total</span>
                    <span className="font-mono tabular-nums">
                      {formatCurrency(total)}
                    </span>
                  </div>
                </div>
              </div>
            );
          }}
        />
        <ChartLegend content={<ChartLegendContent nameKey="dataKey" />} />
        {uniqueCategories.map((cat, i) => (
          <Bar
            key={cat.id}
            dataKey={categoryKey(cat.id)}
            stackId="byCategory"
            fill={CHART_COLORS[i % CHART_COLORS.length]}
            radius={[0, 0, 0, 0]}
          />
        ))}
      </BarChart>
    </ChartContainer>
  );
}
