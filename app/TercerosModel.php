<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TercerosModel extends Model
{
    protected $table = "tercero";
    protected $primaryKey = "idTercero";
    protected $fillable = [
        'privado',
        'fk_actividad_economica',
        'naturalezaTributaria',
        'razonSocial',
        'fkTipoIdentificacion',
        'numeroIdentificacion',
        'fkEstado',
        'direccion',
        // 'fkUbicacion',
        'telefono',
        'fax',
        'correo',
        'codigoTercero',
        'fkTipoAporteSeguridadSocial',
        'codigoSuperIntendencia',
        'primerNombre',
        'segundoNombre',
        'primerApellido',
        'segundoApellido'
    ];
    public $timestamps = false;
}
