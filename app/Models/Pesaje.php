<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pesaje extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'medida_peso_id',
        'peso_total',
        'estado_id',
        'solicitud_id',
        'cuenta_id',
        'fecha_creacion',
        'fecha_inicio',
        'fecha_cierre'
    ];

    protected $dates = [
        'fecha_creacion',
        'fecha_inicio',
        'fecha_cierre',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Relación con MedidaPeso
     */
    public function medidaPeso()
    {
        return $this->belongsTo(MedidaPeso::class);
    }

    /**
     * Relación con Estado
     */
    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }

    /**
     * Relación con SolicitudPesaje
     */
    public function solicitudPesaje()
    {
        return $this->belongsTo(SolicitudPesaje::class, 'solicitud_id');
    }

    /**
     * Relación con Cuenta
     */
    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class);
    }

    /**
     * Relación con Parcialidades
     */
    public function parcialidades()
    {
        return $this->hasMany(Parcialidad::class);
    }
}