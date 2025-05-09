<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SolicitudPesaje extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'solicitudes_pesaje';

    protected $fillable = [
        'cantidad_total', 'medida_peso_id', 'tolerancia', 'precio_unitario',
        'cantidad_parcialidades', 'estado_id', 'agricultor_id'
    ];

    public function agricultor()
    {
        return $this->belongsTo(Agricultor::class);
    }

    public function medidaPeso()
    {
        return $this->belongsTo(MedidaPeso::class);
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }

    public function pesaje()
    {
        return $this->hasOne(Pesaje::class, 'solicitud_id');
    }

    public function cuenta()
    {
        return $this->hasOne(Cuenta::class, 'solicitud_id');
    }
}