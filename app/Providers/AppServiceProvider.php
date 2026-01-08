<?php

namespace App\Providers;

use App\Enums\TransactionTypeEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Builder::macro('whereCategoryType', function (TransactionTypeEnum $type): Builder {
            /** @var Builder $this */
            return $this->whereHas('category', function ($query) use ($type) {
                $query->where('type', $type);
            });
        });
    }
}
