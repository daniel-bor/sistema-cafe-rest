<?php


namespace App\Http\Controllers;

use App\Models\Beneficio;
use App\Models\Parcialidad;
use App\Models\Pesaje;
use App\Models\Cuenta;
use App\Models\Estado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BeneficioController extends Controller
{
    /**
     * Perfil del beneficio
     */
    public function perfil()
    {
        $beneficio = Beneficio::where('user_id', Auth::id())->first();

        if (!$beneficio) {
            return response()->json(['error' => 'Beneficio no encontrado'], 404);
        }

        return response()->json($beneficio);
    }

    /**
     * Listar parcialidades pendientes de aprobación
     */
    public function parcialidadesPendientes()
    {
        $estadoPendiente = Estado::where('nombre', 'Pendiente')
                                ->where('contexto', 'parcialidad')
                                ->firstOrFail()->id;
                                
        $parcialidades = Parcialidad::where('estado_id', $estadoPendiente)
                                   ->with(['pesaje.agricultor', 'transporte', 'transportista', 'estado'])
                                   ->get();
                                     
        return response()->json($parcialidades);
    }

    /**
     * Aprobar parcialidad
     */
    public function aprobarParcialidad(Request $request, $id)
    {
    $beneficio = Auth::user()->beneficio;

    if (!$beneficio) {
        return response()->json(['error' => 'Perfil de beneficio no encontrado'], 404);
    }
    
    DB::beginTransaction();
    
    try {
        // Obtener estados necesarios
        $estadoParcialidadAprobada = Estado::where('nombre', 'Aprobada')
                                       ->where('contexto', 'parcialidad')
                                       ->firstOrFail()->id;
        
        $estadoPendiente = Estado::where('nombre', 'Pendiente')
                                ->where('contexto', 'parcialidad')
                                ->firstOrFail()->id;
        
        $estadoPesajeAprobado = Estado::where('nombre', 'Aprobado')
                                   ->where('contexto', 'pesaje')
                                   ->firstOrFail()->id;
        
        $estadoCuentaCreada = Estado::where('nombre', 'Creada')
                                 ->where('contexto', 'cuenta')
                                 ->firstOrFail()->id;
        
        // Buscar la parcialidad
        $parcialidad = Parcialidad::findOrFail($id);
        
        // CORRECCIÓN: Verificar que la parcialidad esté en estado pendiente
        if ($parcialidad->estado_id != $estadoPendiente) {
            return response()->json([
                'message' => 'Esta parcialidad ya ha sido procesada previamente',
                'estado_actual' => Estado::find($parcialidad->estado_id)->nombre
            ], 400);
        }
        
        $pesaje = Pesaje::findOrFail($parcialidad->pesaje_id);
        
        // Actualizar la parcialidad
        $parcialidad->estado_id = $estadoParcialidadAprobada;
        $parcialidad->save();
        
        // Actualizar el peso total del pesaje
        $pesaje->peso_total = $pesaje->peso_total + $parcialidad->peso;
        
        // Verificar si ya existe una cuenta para este pesaje
        if ($pesaje->cuenta_id) {
            // Ya existe cuenta, solo guardar el pesaje con el peso actualizado
            $pesaje->save();
            
            DB::commit();
            
            return response()->json([
                'message' => 'Parcialidad adicional aprobada correctamente',
                'parcialidad' => $parcialidad,
                'pesaje' => $pesaje
            ], 200);
        }
        
        // Es la primera parcialidad aprobada, crear cuenta
        // Generar número de cuenta único
        $noCuenta = 'CTA-' . date('Ymd') . '-' . str_pad($pesaje->id, 5, '0', STR_PAD_LEFT);
        
        // Crear la cuenta asociada (estado: Creada)
        $cuenta = Cuenta::create([
            'no_cuenta' => $noCuenta,
            'estado_id' => $estadoCuentaCreada, // Estado "Creada"
            'agricultor_id' => $pesaje->agricultor_id,
        ]);
        
        // Actualizar el pesaje (estado: Aprobado)
        $pesaje->estado_id = $estadoPesajeAprobado; // Estado "Aprobado"
        $pesaje->cuenta_id = $cuenta->id;
        $pesaje->fecha_inicio = now();
        $pesaje->save();
        
        DB::commit();
        
        return response()->json([
            'message' => 'Primera parcialidad aprobada y cuenta creada con éxito',
            'parcialidad' => $parcialidad,
            'pesaje' => $pesaje,
            'cuenta' => $cuenta
        ], 200);
        
    } catch (\Exception $e) {
        DB::rollback();
        return response()->json([
            'message' => 'Error al aprobar la parcialidad',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Rechazar parcialidad
     */
    public function rechazarParcialidad(Request $request, $id)
    {
    $request->validate([
        'motivo' => 'required|string|max:255',
    ]);

    $beneficio = Auth::user()->beneficio;
    
    if (!$beneficio) {
        return response()->json(['error' => 'Perfil de beneficio no encontrado'], 404);
    }
    
    DB::beginTransaction();
    
    try {
        // Obtener estado "Rechazada" para parcialidades
        $estadoRechazada = Estado::where('nombre', 'Rechazada')
                                ->where('contexto', 'parcialidad')
                                ->firstOrFail()->id;
                                
        $estadoPendiente = Estado::where('nombre', 'Pendiente')
                                ->where('contexto', 'parcialidad')
                                ->firstOrFail()->id;
                                
        // Buscar la parcialidad
        $parcialidad = Parcialidad::findOrFail($id);
        
        // Verificar que no haya sido procesada previamente
        if ($parcialidad->estado_id != $estadoPendiente) {
            return response()->json([
                'message' => 'Esta parcialidad ya ha sido procesada previamente',
                'estado_actual' => Estado::find($parcialidad->estado_id)->nombre
            ], 400);
        }
        
        // Actualizar la parcialidad
        $parcialidad->estado_id = $estadoRechazada;
        $parcialidad->save();
        
        // Liberar transporte y transportista
        $transporte = $parcialidad->transporte;
        $transportista = $parcialidad->transportista;
        
        // Verificar si el transporte está usado en otras parcialidades del mismo pesaje que no estén rechazadas
        $transporteEnUso = Parcialidad::where('transporte_id', $transporte->id)
                                    ->where('pesaje_id', $parcialidad->pesaje_id)
                                    ->where('id', '!=', $parcialidad->id)
                                    ->whereNotIn('estado_id', [$estadoRechazada])
                                    ->exists();
        
        if (!$transporteEnUso) {
            $transporte->disponible = true;
            $transporte->save();
        }
        
        // Verificar si el transportista está usado en otras parcialidades del mismo pesaje que no estén rechazadas
        $transportistaEnUso = Parcialidad::where('transportista_id', $transportista->id)
                                       ->where('pesaje_id', $parcialidad->pesaje_id)
                                       ->where('id', '!=', $parcialidad->id)
                                       ->whereNotIn('estado_id', [$estadoRechazada])
                                       ->exists();
        
        if (!$transportistaEnUso) {
            $transportista->disponible = true;
            $transportista->save();
        }
        
        DB::commit();
        
        return response()->json([
            'message' => 'Parcialidad rechazada correctamente',
            'motivo' => $request->motivo,
            'parcialidad' => $parcialidad
        ]);
        
    } catch (\Exception $e) {
        DB::rollback();
        return response()->json([
            'message' => 'Error al rechazar la parcialidad',
            'error' => $e->getMessage()
        ], 500);
    }
 }
}