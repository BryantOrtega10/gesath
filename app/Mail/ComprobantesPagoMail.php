<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ComprobantesPagoMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $subject;
    public $mensaje;
    public $sender_mail;
    public $sender_name;
    public $pdf; 

    public function __construct($subject, $mensaje, $sender_mail, $sender_name, $pdf)
    {
        $this->subject = $subject;
        $this->mensaje = $mensaje;
        $this->sender_mail = $sender_mail;
        $this->sender_name = $sender_name;
        $this->pdf = $pdf;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from($this->sender_mail, $this->sender_name)
        //->view('mailViews.comprobantesPago')
        ->subject($this->subject)
        ->attachData($this->pdf, 'Comprobante de pago.pdf', [
            'mime' => 'application/pdf',
        ])
        ->html($this->mensaje);
        
    }
}
