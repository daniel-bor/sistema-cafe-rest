<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Beneficio extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'beneficios';

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'descripcion',
        'user_id'
    ];

    /**
     * Relación con usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}