<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RecuperarPassMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $mail;
    public $token;
    public $empre;

    public function __construct(
        $email,
        $token,
        $empre
    )
    {
        $this->email = $email;
        $this->token = $token;
        $this->empre = $empre;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from("mdc@web-html.com", 'Gesath')
            ->subject('Solicitud recuperacion contraseña Gesath ('. $this->empre. ')')
            ->markdown('mailViews.solicitudRecuperar')
            ->with([
                'token' => $this->token,
                'nomEmpresa' => $this->empre
            ]);
    }
}
