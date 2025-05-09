<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedidaPeso extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'medidas_peso';

    protected $fillable = [
        'nombre',
        'simbolo'
    ];

    /**
     * Relación con SolicitudesPesaje
     */
    public function solicitudesPesaje()
    {
        return $this->hasMany(SolicitudPesaje::class);
    }

    /**
     * Relación con Pesajes
     */
    public function pesajes()
    {
        return $this->hasMany(Pesaje::class);
    }
}