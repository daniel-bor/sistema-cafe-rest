<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Boleta extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'parcialidad_id',
        'usuario_id',
        'concepto',
        'monto',
        'numero_documento',
        'referencia',
        'observaciones'
    ];

    /**
     * Relación con Parcialidad
     */
    public function parcialidad()
    {
        return $this->belongsTo(Parcialidad::class);
    }

    /**
     * Relación con Usuario que registró la boleta
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}