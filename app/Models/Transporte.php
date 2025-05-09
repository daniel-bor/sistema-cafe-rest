<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transporte extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'placa', 'marca', 'color', 'estado_id', 'disponible', 'agricultor_id'
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