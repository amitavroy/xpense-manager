<?php

namespace App\Console\Commands;

use App\Actions\GenerateMonthlyBillInstancesAction;
use Illuminate\Console\Command;

class GeneratePendingBillInstance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-bills';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate pending bill instances for the current month';

    /**
     * Execute the console command.
     */
    public function handle(GenerateMonthlyBillInstancesAction $action): void
    {
        $action->execute();
    }
}
