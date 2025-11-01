<?php

namespace App\Actions;

use App\Enums\BillFrequencyEnum;
use App\Enums\BillStatusEnum;
use App\Models\Bill;
use App\Models\BillInstance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateMonthlyBillInstancesAction
{
    public function execute(?Carbon $currentDate = null): int
    {
        $currentDate = $currentDate ?? Carbon::now();
        $startOfMonth = $currentDate->copy()->startOfMonth();
        $endOfMonth = $currentDate->copy()->endOfMonth();

        $bills = Bill::query()
            ->active()
            ->autoGenerateBill()
            ->whereBetween('next_payment_date', [$startOfMonth, $endOfMonth])
            ->get();

        $generatedCount = 0;

        $bills->each(function (Bill $bill) use (&$generatedCount) {
            DB::transaction(function () use ($bill, &$generatedCount) {
                $exists = BillInstance::query()
                    ->where('bill_id', $bill->id)
                    ->where('due_date', $bill->next_payment_date)
                    ->exists();

                if ($exists) {
                    return;
                }

                BillInstance::create([
                    'bill_id' => $bill->id,
                    'due_date' => $bill->next_payment_date,
                    'amount' => $bill->default_amount,
                    'status' => BillStatusEnum::PENDING,
                ]);

                $nextPaymentDate = $this->getNextPaymentDate($bill);

                $bill->update([
                    'next_payment_date' => $nextPaymentDate,
                ]);

                $generatedCount++;
            });
        });

        return $generatedCount;
    }

    private function getNextPaymentDate(Bill $bill): Carbon
    {
        $currentDate = Carbon::parse($bill->next_payment_date);

        return match ($bill->frequency) {
            BillFrequencyEnum::WEEKLY => $currentDate->addWeek(),
            BillFrequencyEnum::MONTHLY => $currentDate->addMonth(),
            BillFrequencyEnum::QUARTERLY => $currentDate->addQuarter(),
            BillFrequencyEnum::HALF_YEARLY => $currentDate->addHalfYear(),
            BillFrequencyEnum::YEARLY => $currentDate->addYear(),
            BillFrequencyEnum::CUSTOM => $currentDate->addDays($bill->interval_days),
        };
    }
}
