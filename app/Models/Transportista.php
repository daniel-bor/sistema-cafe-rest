<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transportista extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cui', 
        'nombre_completo', 
        'fecha_nacimiento', 
        'tipo_licencia', 
        'fecha_vencimiento_licencia', 
        'agricultor_id', 
        'estado_id', 
        'disponible', 
        'foto'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_vencimiento_licencia' => 'date',
        'disponible' => 'boolean',
    ];

    public function agricultor()
    {
        return $this->belongsTo(Agricultor::class);
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }
    
    public function parcialidades()
    {
        return $this->hasMany(Parcialidad::class);
    }
}