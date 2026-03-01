<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guest cannot access monthly expenses report', function () {
    $this->get(route('reports.monthly-expenses'))
        ->assertRedirect(route('login'));
});

test('authenticated user can view monthly expenses report', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('reports.monthly-expenses'));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('reports/monthly-expenses')
            ->has('monthlyExpenses', 3)
            ->has('monthlyExpenses.0.month')
            ->has('monthlyExpenses.0.total')
            ->has('monthlyExpenses.0.normal')
            ->has('monthlyExpenses.0.credit_card')
        );
});
