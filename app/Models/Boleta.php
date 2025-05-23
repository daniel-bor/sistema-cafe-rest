<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Boleta extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_boleta',
        'fecha_boleta',
        'parcialidad_id',
        'generada_por', // ID del PesoCabal que generó la boleta
        'observaciones'
    ];

    protected $dates = [
        'fecha_boleta',
        'created_at',
        'updated_at'
    ];

     protected $casts = [
        'fecha_boleta' => 'datetime',
    ];


    /**
     * Relación con Parcialidad
     */
    public function parcialidad()
    {
        return $this->belongsTo(Parcialidad::class);
    }

    /**
     * Relación con PesoCabal
     */
    public function pesoCabal()
    {
        return $this->belongsTo(PesoCabal::class, 'generada_por');
    }
}