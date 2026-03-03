<?php

use App\Models\Bill;
use App\Models\Biller;
use App\Models\BillInstance;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

test('authenticated user can view biller expenses data on monthly expenses report', function () {
    $user = User::factory()->create();

    $category = Category::factory()->create();

    $billerOne = Biller::factory()
        ->for($user)
        ->for($category)
        ->create(['name' => 'Claude']);

    $billerTwo = Biller::factory()
        ->for($user)
        ->for($category)
        ->create(['name' => 'Cursor']);

    $now = Carbon::now()->startOfMonth();

    $billOne = Bill::factory()->for($user)->for($billerOne)->create();
    $billTwo = Bill::factory()->for($user)->for($billerTwo)->create();

    BillInstance::factory()->create([
        'bill_id' => $billOne->id,
        'amount' => 1000,
        'paid_date' => $now->copy()->subMonths(2)->addDays(5),
    ]);

    BillInstance::factory()->create([
        'bill_id' => $billTwo->id,
        'amount' => 1500,
        'paid_date' => $now->copy()->subMonth()->addDays(5),
    ]);

    $response = $this->actingAs($user)->get(route('reports.monthly-expenses', [
        'biller_ids' => [$billerOne->id, $billerTwo->id],
        'biller_months' => 3,
    ]));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('reports/monthly-expenses')
            ->has('billers')
            ->has('billerExpenseData')
            ->has('billerExpenseBillers')
            ->has('selectedBillerIds')
            ->has('billerMonths')
        );
});
