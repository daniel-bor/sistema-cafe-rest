<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agricultor extends Model
{
    use SoftDeletes;

    protected $table = 'agricultores';

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
    //codigo agregado Rzapet
    public function transportes()
    {
        return $this->hasMany(Transporte::class);
    }

    public function transportistas()
    {
        return $this->hasMany(Transportista::class);
    }

    public function solicitudesPesaje()
    {
        return $this->hasMany(SolicitudPesaje::class);
    }

    public function cuentas()
    {
        return $this->hasMany(Cuenta::class);
    }

}
