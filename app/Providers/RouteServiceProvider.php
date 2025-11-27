<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    
    public function boot()
    {
        $this->routes(function () {
            // API routes
            Route::prefix('api')
                ->middleware('api')
                ->group(base_path('routes/api.php'));

            // Web routes (if present)
            if (file_exists(base_path('routes/web.php'))) {
                Route::middleware('web')
                    ->group(base_path('routes/web.php'));
            }
        });
    }
}
