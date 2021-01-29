<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EstadoModel extends Model
{
    protected $table = "estado";
    protected $primaryKey = "idestado";
    protected $fillable = [
        'nombre', 'estadoActivo', 'clase'
    ];
}
