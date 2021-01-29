<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Directiva para convertir numero a numero separado
        Blade::directive('moneda', function ($money) {
            $moneda = (int) $money;
            dd($moneda);
            return '<?php echo number_format ('.$moneda.', 2 , "." ,  "," ); ?>';
        });
    }
}
