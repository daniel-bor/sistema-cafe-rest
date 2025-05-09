<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Parcialidad extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'parcialidades';

    protected $fillable = [
        'pesaje_id',
        'transporte_id',
        'transportista_id',
        'peso',
        'tipo_medida',
        'fecha_recepcion',
        'estado_id',
        'codigo_qr'
    ];

    protected $dates = [
        'fecha_recepcion',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Relación con Pesaje
     */
    public function pesaje()
    {
        return $this->belongsTo(Pesaje::class);
    }

    /**
     * Relación con Transporte
     */
    public function transporte()
    {
        return $this->belongsTo(Transporte::class);
    }

    /**
     * Relación con Transportista
     */
    public function transportista()
    {
        return $this->belongsTo(Transportista::class);
    }

    /**
     * Relación con Estado
     */
    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }

    /**
     * Relación con Boletas
     */
    public function boletas()
    {
        return $this->hasMany(Boleta::class);
    }
}