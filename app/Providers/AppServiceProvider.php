<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Laboratory;

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
        View::composer('panel.sidebar', function ($view) {
            $user = auth()->user();

            if (! $user) {
                $view->with('laboratories', collect());
                return;
            }

            if ($user->role === 'spv inventory') {
                $laboratories = Laboratory::orderBy('lab_name')->get();
            } else {
                $laboratories = $user->labs()
                    ->orderBy('lab_name')
                    ->get();
            }

            $view->with('laboratories', $laboratories);
        });
    }
}
