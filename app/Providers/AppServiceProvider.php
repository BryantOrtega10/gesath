<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('path.public', function() {
            return base_path().'/public_html';
          });
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
            return '<?php echo number_format ('.$moneda.', 2 , "." ,  "," ); ?>';
        });
        
        $menus = DB::table("menu","m")->orderBy("m.fkMenu")->get();
        $arrMenu = array();
        foreach($menus as $itemMenu){
            $itemMenu->subItems = array();
            if(isset($itemMenu->fkMenu)){
                if(isset($arrMenu[$itemMenu->fkMenu])){
                    array_push($arrMenu[$itemMenu->fkMenu]->subItems, $itemMenu);
                }
                else{
                    foreach($arrMenu as $menuLv1){
                        foreach($menuLv1->subItems as $menuLv2){
                            if($menuLv2->idMenu == $itemMenu->fkMenu){
                                array_push($menuLv2->subItems, $itemMenu);
                                break;
                            }
                        }
                    }
                }
            }
            else{
                $arrMenu[$itemMenu->idMenu] = $itemMenu;
            }
        }
        
        view()->share('arrMenu', $arrMenu);


    }
}
