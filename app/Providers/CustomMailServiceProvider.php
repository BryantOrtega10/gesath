<?php
namespace App\Providers;

use Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Mail\MailServiceProvider;

class CustomMailServiceProvider extends MailServiceProvider
{        /**
        * Bootstrap the application services.
        *
        * @return void
        */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $smtp = DB::table('smtp_config')->first();
        // dd($smtp);
        if ($smtp) {
            $config = array(
                'driver'     => 'SMTP',
                'host'       => $smtp->smtp_host,
                'port'       => $smtp->smtp_port,
                'from'       => array(
                    'address' => $smtp->smtp_mail_envia,
                    'name' => $smtp->smtp_nombre_envia
                ),
                'encryption' => $smtp->smtp_encrypt,
                'username'   => $smtp->smtp_username,
                'password'   => $smtp->smtp_password
            );
            Config::set('mail', $config);
        }
    }
}