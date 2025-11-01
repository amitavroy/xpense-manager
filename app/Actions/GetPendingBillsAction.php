<?php

namespace App\Actions;

use App\Enums\BillStatusEnum;
use App\Models\BillInstance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class GetPendingBillsAction
{
    public function execute(?Carbon $currentDate = null): Collection
    {
        $currentDate = $currentDate ?? Carbon::now();
        $startOfDay = $currentDate->copy()->startOfMonth();
        $endOfDay = $currentDate->copy()->endOfMonth();

        return BillInstance::query()
            ->with(['bill.biller'])
            ->where('status', BillStatusEnum::PENDING)
            ->whereBetween('due_date', [$startOfDay, $endOfDay])
            ->get();
    }
}
