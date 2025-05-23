<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PesoCabal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'peso_cabals';

    protected $fillable = [
        'nombre',
        'codigo_empleado',
        'area',
        'telefono',
        'user_id'
    ];

    /**
     * Relación con usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Relación con parcialidades verificadas
     */
    public function parcialidadesVerificadas()
    {
        return $this->hasMany(Parcialidad::class, 'verificada_por');
    }
    
    /**
     * Relación con boletas generadas
     */
    public function boletas()
    {
        return $this->hasMany(Boleta::class, 'generada_por');
    }
}