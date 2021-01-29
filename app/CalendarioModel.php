<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CalendarioModel extends Model
{
    protected $table = "calendario";
    protected $primaryKey = "idCalendario";
    protected $fillable = [
        'fecha',
        'fechaInicioSemana',
        'fechaFinSemana'
    ];
    public $timestamps = false;
}
