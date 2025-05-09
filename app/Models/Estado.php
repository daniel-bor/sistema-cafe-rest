<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Estado extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'estados';

    protected $fillable = [
        'nombre',
        'contexto'
    ];

    /**
     * Relación con Transportes
     */
    public function transportes()
    {
        return $this->hasMany(Transporte::class);
    }

    /**
     * Relación con Transportistas
     */
    public function transportistas()
    {
        return $this->hasMany(Transportista::class);
    }

    /**
     * Relación con SolicitudesPesaje
     */
    public function solicitudesPesaje()
    {
        return $this->hasMany(SolicitudPesaje::class);
    }

    /**
     * Relación con Cuentas
     */
    public function cuentas()
    {
        return $this->hasMany(Cuenta::class);
    }

    /**
     * Relación con Pesajes
     */
    public function pesajes()
    {
        return $this->hasMany(Pesaje::class);
    }

    /**
     * Relación con Parcialidades
     */
    public function parcialidades()
    {
        return $this->hasMany(Parcialidad::class);
    }

    /**
     * Obtener estados por contexto específico
     *
     * @param string $contexto
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function porContexto($contexto)
    {
        return self::where('contexto', $contexto)->get();
    }
}