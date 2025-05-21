<?php

namespace App\Http\Controllers;

use App\Models\Pesaje;
use App\Models\Estado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PesajeController extends Controller
{
    /**
     * Listar pesajes del agricultor
     */
    public function index()
    {
        $agricultor = Auth::user()->agricultor;
        $pesajes = Pesaje::where('agricultor_id', $agricultor->id)
                        ->with(['medidaPeso', 'estado', 'cuenta'])
                        ->get();
        
        return response()->json($pesajes);
    }

    /**
     * Crear un nuevo pesaje
     */
    public function store(Request $request)
    {
        $request->validate([
            'medida_peso_id' => 'required|exists:medidas_peso,id',
            'cantidad_total' => 'required|numeric|min:0',
            'tolerancia' => 'required|numeric|min:0|max:100',
            'precio_unitario' => 'required|numeric|min:0',
            'cantidad_parcialidades' => 'required|integer|min:1',
        ]);

        $agricultor = Auth::user()->agricultor;
        
        // Obtener estado "Pendiente" para pesajes
        $estadoPendiente = Estado::where('nombre', 'Pendiente')
                                ->where('contexto', 'pesaje')
                                ->firstOrFail()->id;
        
        try {
            // Crear el pesaje
            $pesaje = Pesaje::create([
                'agricultor_id' => $agricultor->id,
                'medida_peso_id' => $request->medida_peso_id,
                'peso_total' => 0,
                'cantidad_total' => $request->cantidad_total,
                'tolerancia' => $request->tolerancia,
                'precio_unitario' => $request->precio_unitario,
                'cantidad_parcialidades' => $request->cantidad_parcialidades,
                'estado_id' => $estadoPendiente,
                'fecha_creacion' => now(),
            ]);
            
            return response()->json([
                'message' => 'Pesaje creado correctamente. Ahora puede agregar la primera parcialidad.',
                'pesaje' => $pesaje
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear el pesaje',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un pesaje específico
     */
    public function show($id)
    {
        $agricultor = Auth::user()->agricultor;
        $pesaje = Pesaje::where('id', $id)
                      ->where('agricultor_id', $agricultor->id)
                      ->with(['medidaPeso', 'estado', 'cuenta', 'parcialidades.estado'])
                      ->firstOrFail();
        
        return response()->json($pesaje);
    }

    /**
     * Actualizar pesaje
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'cantidad_total' => 'numeric|min:0',
            'tolerancia' => 'numeric|min:0|max:100',
            'precio_unitario' => 'numeric|min:0',
            'cantidad_parcialidades' => 'integer|min:1',
        ]);

        $agricultor = Auth::user()->agricultor;
        $pesaje = Pesaje::where('id', $id)
                      ->where('agricultor_id', $agricultor->id)
                      ->firstOrFail();
        
        // Solo permitir actualizar si está en estado pendiente
        if ($pesaje->estado->nombre != 'Pendiente') {
            return response()->json([
                'message' => 'No se puede actualizar un pesaje que ya no está en estado pendiente'
            ], 400);
        }
        
        $pesaje->update($request->only([
            'cantidad_total', 'tolerancia', 'precio_unitario', 'cantidad_parcialidades'
        ]));
        
        return response()->json([
            'message' => 'Pesaje actualizado correctamente',
            'pesaje' => $pesaje
        ]);
    }

    /**
     * Eliminar pesaje
     */
    public function destroy($id)
    {
        $agricultor = Auth::user()->agricultor;
        $pesaje = Pesaje::where('id', $id)
                      ->where('agricultor_id', $agricultor->id)
                      ->firstOrFail();
        
        // Solo permitir eliminar si está en estado pendiente
        if ($pesaje->estado->nombre != 'Pendiente') {
            return response()->json([
                'message' => 'No se puede eliminar un pesaje que ya no está en estado pendiente'
            ], 400);
        }
        
        $pesaje->delete();
        
        return response()->json([
            'message' => 'Pesaje eliminado correctamente'
        ]);
    }
}