<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Solicitud;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }
    
    public function boot()
    {
        Paginator::useBootstrapFive();

        view()->composer('components.navigation-menu', function ($view) {

            $user = Auth::user();
            if (!$user) {
                $view->with('solicitudesPendientes', 0);
                return;
            }

            // ===============================
            // 🟡 ADMIN O USUARIO SIN BODEGA
            // ===============================
            if (!$user || !$user->bodega_id) {

                $solicitudesPendientes = Solicitud::where('estado', 'pendiente')->count();

                $view->with('solicitudesPendientes', $solicitudesPendientes);
                return;
            }

            // ===============================
            // 🟢 USUARIO CON BODEGA
            // ===============================
            $bodegaId = $user->bodega_id;

            $solicitudesPendientes = Solicitud::where('bodega_id', $bodegaId)
                ->where('estado', 'aprobado')
                ->where(function ($q) {
                    $q->whereNull('resolucion')
                      ->orWhere('resolucion', 'A_LA_ESPERA'); // 🔥 SOLO botones amarillos
                })
                ->count();

            $view->with('solicitudesPendientes', $solicitudesPendientes);
        });
    }
}
