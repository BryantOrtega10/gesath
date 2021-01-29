<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoCompaniaModel extends Model
{
    protected $table = "tipocompania";
    protected $primaryKey = "idtipoCompania";
    protected $fillable = [
        'nombre'
    ];
}
