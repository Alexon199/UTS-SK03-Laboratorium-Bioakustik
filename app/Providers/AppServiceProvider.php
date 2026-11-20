<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Pakai tampilan pagination buatan sendiri (tanpa Tailwind/Vite).
        Paginator::defaultView('vendor.pagination.bioakustik');
    }
}
