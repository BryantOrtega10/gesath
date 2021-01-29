<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoIdentificacionModel extends Model
{
    protected $table = "tipoidentificacion";
    protected $primaryKey = "idtipoIdentificacion";
    protected $fillable = [
        'nombre'
    ];
}
