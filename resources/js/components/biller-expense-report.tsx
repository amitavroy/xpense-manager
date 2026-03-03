import BillerExpenseLineChart from '@/components/biller-expense-line-chart';
import { Button } from '@/components/ui/button';
import {
  DropdownMenu,
  DropdownMenuCheckboxItem,
  DropdownMenuContent,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import type { BillerExpenseDataPoint } from '@/types';
import { ChevronDownIcon } from 'lucide-react';

const PERIOD_OPTIONS = [
  { value: '1', label: '1 Month' },
  { value: '2', label: '2 Months' },
  { value: '3', label: '3 Months' },
  { value: '6', label: '6 Months' },
  { value: '12', label: '12 Months' },
];

interface BillerExpenseReportProps {
  billers: { id: number; name: string }[];
  billerExpenseData: BillerExpenseDataPoint[];
  billerExpenseBillers: { id: number; name: string }[];
  selectedBillerIds: number[];
  billerMonths: number;
  onMonthsChange: (months: number) => void;
  onBillerToggle: (id: number) => void;
}

export default function BillerExpenseReport({
  billers,
  billerExpenseData,
  billerExpenseBillers,
  selectedBillerIds,
  billerMonths,
  onMonthsChange,
  onBillerToggle,
}: BillerExpenseReportProps) {
  return (
    <div className="relative flex min-w-0 flex-col gap-3 overflow-hidden rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <h2 className="text-sm font-medium">Biller expenses</h2>
        <div className="flex items-center gap-2">
          <Select
            value={String(billerMonths ?? 3)}
            onValueChange={(value) => onMonthsChange(Number(value) || 3)}
          >
            <SelectTrigger className="w-40">
              <SelectValue placeholder="Select period" />
            </SelectTrigger>
            <SelectContent>
              {PERIOD_OPTIONS.map((option) => (
                <SelectItem key={option.value} value={option.value}>
                  {option.label}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <Button
                variant="outline"
                className="h-9 min-w-40 justify-between gap-2 border border-input bg-transparent px-3 py-2 text-sm shadow-xs data-placeholder:text-muted-foreground"
              >
                <span className="truncate">
                  {billers.length === 0
                    ? 'No billers'
                    : (selectedBillerIds?.length ?? 0) === 0
                      ? 'Select billers'
                      : (selectedBillerIds?.length ?? 0) === 1
                        ? (billers.find((b) => b.id === selectedBillerIds?.[0])
                            ?.name ?? '1 biller')
                        : `${selectedBillerIds?.length ?? 0} billers selected`}
                </span>
                <ChevronDownIcon className="size-4 shrink-0 opacity-50" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent
              align="end"
              className="max-h-64 overflow-y-auto"
            >
              {billers.length === 0 ? (
                <div className="px-2 py-3 text-sm text-muted-foreground">
                  You do not have any active billers yet.
                </div>
              ) : (
                billers.map((biller) => {
                  const checked =
                    selectedBillerIds?.includes(biller.id) ?? false;
                  return (
                    <DropdownMenuCheckboxItem
                      key={biller.id}
                      checked={checked}
                      onCheckedChange={() => onBillerToggle(biller.id)}
                    >
                      {biller.name}
                    </DropdownMenuCheckboxItem>
                  );
                })
              )}
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </div>
      <div className="relative min-w-0 overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
        <BillerExpenseLineChart
          data={billerExpenseData}
          billers={billerExpenseBillers}
        />
      </div>
    </div>
  );
}
