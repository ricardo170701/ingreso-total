<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\User::class => \App\Policies\UserPolicy::class,
        \App\Models\Puerta::class => \App\Policies\PuertaPolicy::class,
        \App\Models\Mantenimiento::class => \App\Policies\MantenimientoPolicy::class,
        \App\Models\Cargo::class => \App\Policies\CargoPolicy::class,
        \App\Models\ProtocolRun::class => \App\Policies\ProtocolRunPolicy::class,
        \App\Models\Departamento::class => \App\Policies\DepartamentoPolicy::class,
        \App\Models\Ups::class => \App\Policies\UpsPolicy::class,
        \App\Models\UpsMantenimiento::class => \App\Policies\UpsMantenimientoPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
