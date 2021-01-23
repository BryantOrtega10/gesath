<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SMTPConfigModel extends Model
{
    protected $table = "smtp_config";
    protected $primaryKey = "id_smpt";
    protected $fillable = [
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encrypt',
        'smtp_mail_envia',
        'smtp_nombre_envia'
    ];
    public $timestamps = false;
}