<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Pagination\Paginator;

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
        // En producción forzar HTTPS para evitar Mixed Content (p. ej. paginado con http://)
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        } elseif (config('app.url') && str_starts_with(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // Evitar URLs absolutas (http/https) en links de paginación: devolver path relativo.
        // Esto previene errores de Mixed Content cuando la app corre detrás de un proxy.
        Paginator::currentPathResolver(fn() => request()->getPathInfo());
    }
}
