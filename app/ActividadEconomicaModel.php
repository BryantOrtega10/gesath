<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActividadEconomicaModel extends Model
{
    protected $table = "actividadeconomica";
    protected $primaryKey = "idactividadEconomica";
    protected $fillable = [
        'nombre'
    ];
}
