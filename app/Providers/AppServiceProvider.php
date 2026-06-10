<?php

namespace App\Providers;

use App\Models\Laboratory;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

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
