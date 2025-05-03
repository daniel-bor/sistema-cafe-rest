<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agricultor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nit',
        'nombre',
        'apellido',
        'telefono',
        'direccion',
        'observaciones',
        'user_id'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
