<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoAportanteModel extends Model
{
    protected $table = "tipoaportante";
    protected $primaryKey = "idtipoAportante";
    protected $fillable = [
        'nombre'
    ];
}
