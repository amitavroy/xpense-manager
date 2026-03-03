import {
  ChartContainer,
  ChartLegend,
  ChartLegendContent,
  ChartTooltip,
  type ChartConfig,
} from '@/components/ui/chart';
import { formatCurrency, formatCurrencyShort } from '@/lib/utils';
import type { BillerExpenseDataPoint } from '@/types';
import { CartesianGrid, Line, LineChart, XAxis, YAxis } from 'recharts';

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

function billerKey(id: number): string {
  return `biller_${id}`;
}

interface SimpleBiller {
  id: number;
  name: string;
}

interface BillerExpenseLineChartProps {
  data: BillerExpenseDataPoint[];
  billers: SimpleBiller[];
}

export default function BillerExpenseLineChart({
  data,
  billers,
}: BillerExpenseLineChartProps) {
  const hasData =
    billers.length > 0 &&
    data.length > 0 &&
    data.some((row) =>
      billers.some((biller) => {
        const key = billerKey(biller.id);
        const value = row[key] as number | string | undefined;
        return typeof value === 'number' && value > 0;
      }),
    );

  if (!hasData) {
    return (
      <div
        className="flex flex-col items-center justify-center gap-2 p-8 text-center text-muted-foreground"
        aria-label="No biller expense data for this period"
      >
        <p className="text-sm">No biller expense data for this period.</p>
      </div>
    );
  }

  const chartConfig: ChartConfig = {
    month: { label: 'Month' },
    ...Object.fromEntries(
      billers.map((biller, index) => [
        billerKey(biller.id),
        {
          label: biller.name,
          color: CHART_COLORS[index % CHART_COLORS.length],
        },
      ]),
    ),
  };

  const chartData = data.map((row) => ({
    ...row,
    monthLabel: formatMonthLabel(row.month),
  }));

  return (
    <ChartContainer
      config={chartConfig}
      className="h-[300px] w-full min-w-0 overflow-hidden"
    >
      <LineChart
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
          tickFormatter={(value) => formatCurrencyShort(value as number)}
        />
        <ChartTooltip
          content={({ active, payload }) => {
            if (!active || !payload?.length) return null;
            const row = payload[0].payload as BillerExpenseDataPoint & {
              monthLabel: string;
            };
            let total = 0;
            const lines = billers
              .map((biller) => {
                const key = billerKey(biller.id);
                const value = (row[key] as number | undefined) ?? 0;
                if (!value || value <= 0) {
                  return null;
                }
                total += value;
                return { name: biller.name, value };
              })
              .filter(
                (entry): entry is { name: string; value: number } =>
                  entry !== null,
              );

            if (!lines.length) {
              return null;
            }

            return (
              <div className="grid min-w-32 items-start gap-1.5 rounded-lg border border-border/50 bg-background px-2.5 py-1.5 text-xs shadow-xl">
                <div className="font-medium">{row.monthLabel}</div>
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
        {billers.map((biller, index) => (
          <Line
            key={biller.id}
            type="monotone"
            dataKey={billerKey(biller.id)}
            stroke={CHART_COLORS[index % CHART_COLORS.length]}
            strokeWidth={2}
            dot={false}
            activeDot={{ r: 4 }}
          />
        ))}
      </LineChart>
    </ChartContainer>
  );
}
