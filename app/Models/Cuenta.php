<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cuenta extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cuentas';

    protected $fillable = [
        'no_cuenta',
        'estado_id',
        'agricultor_id',
        'solicitud_id',
        'tolerancia'
    ];

    /**
     * Relación con Estado
     */
    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }

    /**
     * Relación con Agricultor
     */
    public function agricultor()
    {
        return $this->belongsTo(Agricultor::class);
    }

    /**
     * Relación con SolicitudPesaje
     */
    public function solicitudPesaje()
    {
        return $this->belongsTo(SolicitudPesaje::class, 'solicitud_id');
    }

    /**
     * Relación con Pesaje
     */
    public function pesaje()
    {
        return $this->hasOne(Pesaje::class);
    }
}