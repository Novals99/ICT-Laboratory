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
            $view->with('laboratories', Laboratory::orderBy('lab_name')->get());
        });
    }
}
