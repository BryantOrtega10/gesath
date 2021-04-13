<?php
namespace App\Customs;

use Illuminate\Mail\TransportManager;
use App\SMTPConfigModel;

class CustomTransportManager extends TransportManager {

    /**
     * Create a new manager instance.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
        $smtp = SMTPConfigModel::select(['*'])->first();
        if( $smtp ){
            $this->app['config']['mail'] = [
                'driver'        => 'SMTP',
                'host'          => $smtp->smtp_host,
                'port'          => $smtp->smtp_port,
                'from'          => [
                    'address'   => $smtp->smtp_mail_envia,
                    'name'      => $smtp->smtp_nombre_envia
                ],
                'encryption'    => $smtp->smtp_encrypt,
                'username'      => $smtp->smtp_username,
                'password'      => $smtp->smtp_password
           ];
       }

    }
} 