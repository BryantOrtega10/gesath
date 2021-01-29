<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GrupoConceptoConcepto extends Model
{
    protected $table = 'grupoconcepto_concepto';
	protected $primaryKey = 'idGrupoConcepto ';
	protected $fillable = ['fkGrupoConcepto', 'fkConcepto'];
	public $timestamps = false;
}
