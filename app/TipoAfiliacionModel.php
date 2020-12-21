<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoAfiliacionModel extends Model
{
    protected $table = "tipoaporteseguridadsocial";
    protected $primaryKey = "idTipoAporteSeguridadSocial";
    protected $fillable = [
        'nombre'
    ];
}
