<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmpresaModel extends Model
{
    protected $table = "empresa";
    protected $primaryKey = "idempresa";
    protected $fillable = [
        'fkTipoCompania',
        'fkTipoAportante',
        'razonSocial',
        'sigla',
        'dominio',
        'fkTipoIdentificacion',
        'fkActividadEconomica',
        'fkUbicacion',
        'direccion',
        'paginaWeb',
        'telefonoFijo',
        'celular',
        'email1',
        'email2',
        'exento',
        'representanteLegal',
        'docRepresentante',
        'numDocRepresentante',
        'logoEmpresa',
        'vacacionesNegativas'
    ];
    public $timestamps = false;
}
