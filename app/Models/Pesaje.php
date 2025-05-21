<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pesaje extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'agricultor_id',
        'medida_peso_id',
        'peso_total',
        'cantidad_total',
        'tolerancia',
        'precio_unitario',
        'cantidad_parcialidades',
        'estado_id',
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
     * Relación con Agricultor
     */
    public function agricultor()
    {
        return $this->belongsTo(Agricultor::class);
    }

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

    /**
     * Verifica si el pesaje está dentro de la tolerancia permitida
     */
    public function dentroDeTolerancia()
    {
        $pesoMaximoPermitido = $this->cantidad_total * (1 + ($this->tolerancia / 100));
        return $this->peso_total <= $pesoMaximoPermitido;
    }
    
    /**
     * Verifica si el pesaje ha alcanzado la cantidad total
     */
    public function pesoCompletado()
    {
        return $this->peso_total >= $this->cantidad_total;
    }
    
    /**
     * Verifica si se han completado todas las parcialidades requeridas
     */
    public function parcialidadesCompletadas()
    {
        $parcialidadesAprobadas = $this->parcialidades()
            ->whereHas('estado', function($q) {
                $q->where('nombre', 'Aprobada')
                  ->orWhere('nombre', 'Verificada');
            })
            ->count();
            
        return $parcialidadesAprobadas >= $this->cantidad_parcialidades;
    }
}