<?php

namespace App\Http\Controllers;

use App\Models\Agricultor;
use App\Models\SolicitudPesaje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgricultorController extends Controller
{
    /**
     * Mostrar perfil del agricultor autenticado
     */
    public function perfil()
    {
        $agricultor = Agricultor::where('user_id', Auth::id())->first();

        if (!$agricultor) {
            return response()->json(['error' => 'Agricultor no encontrado'], 404);
        }

        return response()->json($agricultor);
    }

    /**
     * Mostrar todas las solicitudes del agricultor autenticado
     */
    public function misSolicitudes()
    {
        $agricultor = Agricultor::where('user_id', Auth::id())->first();

        if (!$agricultor) {
            return response()->json(['error' => 'Agricultor no encontrado'], 404);
        }

        $solicitudes = SolicitudPesaje::where('agricultor_id', $agricultor->id)->with('estado')->get();

        return response()->json($solicitudes);
    }

    /**
     * Crear una solicitud de pesaje
     */
    public function enviarSolicitud(Request $request)
    {
        $request->validate([
            'cantidad_total' => 'required|numeric|min:0',
            'medida_peso_id' => 'required|exists:medidas_peso,id',
            'tolerancia' => 'required|numeric|min:0|max:100',
            'precio_unitario' => 'required|numeric|min:0',
            'cantidad_parcialidades' => 'required|integer|min:1'
        ]);

        $agricultor = Agricultor::where('user_id', Auth::id())->first();

        if (!$agricultor) {
            return response()->json(['error' => 'Agricultor no encontrado'], 404);
        }

        $solicitud = SolicitudPesaje::create([
            'cantidad_total' => $request->cantidad_total,
            'medida_peso_id' => $request->medida_peso_id,
            'tolerancia' => $request->tolerancia,
            'precio_unitario' => $request->precio_unitario,
            'cantidad_parcialidades' => $request->cantidad_parcialidades,
            'estado_id' => 1, // Estado inicial (ej: PENDIENTE)
            'agricultor_id' => $agricultor->id,
        ]);

        return response()->json([
            'message' => 'Solicitud enviada correctamente',
            'solicitud' => $solicitud
        ], 201);
    }
}
