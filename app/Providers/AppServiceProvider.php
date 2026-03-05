<?php

namespace App\Providers;

use App\Models\TouristRegion;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        View::composer(['layouts.public', 'components.layouts.public', 'livewire.layout.navigation'], function ($view): void {
            if (! Schema::hasTable('tourist_regions')) {
                $view->with('publicTouristRegions', collect());

                return;
            }

            $regions = TouristRegion::query()
                ->published()
                ->orderBy('name')
                ->get(['id', 'name', 'slug']);

            $view->with('publicTouristRegions', $regions);
        });
    }
}
