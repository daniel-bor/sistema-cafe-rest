<?php

namespace App\Http\Controllers;

use App\Models\Parcialidad;
use App\Models\Pesaje;
use App\Models\Cuenta;
use App\Models\Estado;
use App\Models\Transporte;
use App\Models\Transportista;
use App\Models\SolicitudPesaje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ParcialidadController extends Controller
{
    public function index()
    {
        $agricultor = Auth::user()->agricultor;
        $parcialidades = Parcialidad::whereHas('pesaje', function($query) use ($agricultor) {
            $query->whereHas('cuenta', function($q) use ($agricultor) {
                $q->where('agricultor_id', $agricultor->id);
            });
        })->with(['transporte', 'transportista', 'estado', 'pesaje'])->get();
        
        return response()->json($parcialidades);
    }

    public function store(Request $request)
    {
        $request->validate([
            'pesaje_id' => 'required|exists:pesajes,id',
            'transporte_id' => 'required|exists:transportes,id',
            'transportista_id' => 'required|exists:transportistas,id',
            'peso' => 'required|numeric|min:0',
            'tipo_medida' => 'required|string|max:20',
        ]);

        $agricultor = Auth::user()->agricultor;
        
        // Verificar que el pesaje pertenezca al agricultor
        $pesaje = Pesaje::where('id', $request->pesaje_id)
                        ->whereHas('cuenta', function($query) use ($agricultor) {
                            $query->where('agricultor_id', $agricultor->id);
                        })
                        ->with(['solicitudPesaje', 'estado', 'medidaPeso'])  // Cargar relaciones necesarias
                        ->first();
        
        if (!$pesaje) {
            return response()->json(['message' => 'El pesaje no existe o no pertenece a este agricultor'], 404);
        }
        
        // Verificar que el estado del pesaje permita nuevas parcialidades
        // Actualizado para usar los nuevos estados
        $estadosPermitidos = ['Sin Peso', 'Pendiente de Verificación', 'Registrada'];
        $estadoActual = $pesaje->estado->nombre;

        if (!in_array($estadoActual, $estadosPermitidos)) {
            return response()->json([
                'message' => 'No se pueden agregar parcialidades en el estado actual del pesaje',
                'estado_actual' => $estadoActual,
                'estados_permitidos' => $estadosPermitidos
            ], 400);
        }
        
        // Verificar que el transporte pertenezca al agricultor
        $transporte = Transporte::where('id', $request->transporte_id)
                               ->where('agricultor_id', $agricultor->id)
                               ->first();
        
        if (!$transporte) {
            return response()->json(['message' => 'El transporte no existe o no pertenece a este agricultor'], 404);
        }
        
        if (!$transporte->disponible) {
            return response()->json(['message' => 'El transporte seleccionado no está disponible actualmente'], 400);
        }
        
        // Verificar que el transportista pertenezca al agricultor
        $transportista = Transportista::where('id', $request->transportista_id)
                                     ->where('agricultor_id', $agricultor->id)
                                     ->first();
        
        if (!$transportista) {
            return response()->json(['message' => 'El transportista no existe o no pertenece a este agricultor'], 404);
        }
        
        if (!$transportista->disponible) {
            return response()->json(['message' => 'El transportista seleccionado no está disponible actualmente'], 400);
        }
        
        // Validar que la medida de peso coincida con la del pesaje
        if ($pesaje->medidaPeso && $request->tipo_medida != $pesaje->medidaPeso->nombre) {
            return response()->json([
                'message' => 'La medida de la parcialidad debe coincidir con la medida del pesaje',
                'medida_pesaje' => $pesaje->medidaPeso->nombre,
                'medida_solicitada' => $request->tipo_medida
            ], 400);
        }
        
        // Verificar límite de parcialidades
        $solicitud = $pesaje->solicitudPesaje;
        $parcialidadesActuales = Parcialidad::where('pesaje_id', $pesaje->id)->count();
        
        if ($parcialidadesActuales >= $solicitud->cantidad_parcialidades) {
            return response()->json([
                'message' => 'No se puede agregar más parcialidades. Ya se alcanzó el límite especificado en la solicitud',
                'parcialidades_actuales' => $parcialidadesActuales,
                'parcialidades_permitidas' => $solicitud->cantidad_parcialidades
            ], 400);
        }
        
        // Verificar límite de peso
        $pesoFuturo = $pesaje->peso_total + $request->peso;
        $pesoMaximoPermitido = $solicitud->cantidad_total * (1 + ($solicitud->tolerancia / 100));
        
        if ($pesoFuturo > $pesoMaximoPermitido) {
            return response()->json([
                'message' => 'No se puede agregar esta parcialidad porque excedería el peso total permitido',
                'peso_actual' => $pesaje->peso_total,
                'peso_a_agregar' => $request->peso,
                'peso_resultante' => $pesoFuturo,
                'peso_maximo' => $pesoMaximoPermitido,
                'cantidad_solicitud' => $solicitud->cantidad_total,
                'tolerancia' => $solicitud->tolerancia . '%'
            ], 400);
        }
        
        // Obtener estado "Pendiente" para parcialidades nuevas
        $estadoPendiente = Estado::whereRaw('LOWER(nombre) = ?', [strtolower('Pendiente')])
                        ->where('contexto', 'parcialidad')
                        ->firstOrFail()->id;
        
        // Generar código QR único
        $codigoQR = 'PAR-' . Str::random(10) . '-' . time();
        
        DB::beginTransaction();
        
        try {
            // Crear la parcialidad
            $parcialidad = Parcialidad::create([
                'pesaje_id' => $request->pesaje_id,
                'transporte_id' => $request->transporte_id,
                'transportista_id' => $request->transportista_id,
                'peso' => $request->peso,
                'tipo_medida' => $request->tipo_medida,
                'estado_id' => $estadoPendiente,
                'codigo_qr' => $codigoQR,
            ]);
            
            // Actualizar el peso total del pesaje
            $pesoActual = $pesaje->peso_total + $request->peso;
            $pesaje->peso_total = $pesoActual;
            
            // Si esta es la primera parcialidad, actualizar estados
            $parcialidadesCount = Parcialidad::where('pesaje_id', $pesaje->id)->count();
            
            if ($parcialidadesCount == 1) {
                // Cambiar estado de solicitud a "En Proceso"
                $estadoSolicitudEnProceso = Estado::where('nombre', 'En Proceso')
                                                 ->where('contexto', 'solicitud')
                                                 ->firstOrFail()->id;
                
                $solicitud = SolicitudPesaje::findOrFail($pesaje->solicitud_id);
                $solicitud->estado_id = $estadoSolicitudEnProceso;
                $solicitud->save();
                
                // Cambiar estado de cuenta a "Cuenta Creada"
                $estadoCuentaCreada = Estado::where('nombre', 'Cuenta Creada')
                                           ->where('contexto', 'cuenta')
                                           ->firstOrFail()->id;
                
                $cuenta = Cuenta::findOrFail($pesaje->cuenta_id);
                $cuenta->estado_id = $estadoCuentaCreada;
                $cuenta->save();
                
                // Actualizar estado del pesaje a "Pendiente de Verificación"
                $estadoPesajePendiente = Estado::where('nombre', 'Pendiente de Verificación')
                                              ->where('contexto', 'pesaje')
                                              ->firstOrFail()->id;
                
                $pesaje->estado_id = $estadoPesajePendiente;
            }
            
            // Verificar criterios de finalización
            $pesoAlcanzado = $pesoActual >= $solicitud->cantidad_total;
            $parcialidadesCompletadas = $parcialidadesCount >= $solicitud->cantidad_parcialidades;
            
            if ($pesoAlcanzado || $parcialidadesCompletadas) {
                // Cambiar estado de solicitud a "Completada"
                $estadoSolicitudCompletada = Estado::where('nombre', 'Completada')
                                                  ->where('contexto', 'solicitud')
                                                  ->firstOrFail()->id;
                
                $solicitud = SolicitudPesaje::findOrFail($pesaje->solicitud_id);
                $solicitud->estado_id = $estadoSolicitudCompletada;
                $solicitud->save();
            }
            
            $pesaje->save();
            
            DB::commit();
            
            // Devolver respuesta con información adicional de trazabilidad
            return response()->json([
                'message' => 'Parcialidad creada con éxito',
                'parcialidad' => $parcialidad,
                'pesaje' => $pesaje->fresh(['estado']),
                'cuenta' => Cuenta::findOrFail($pesaje->cuenta_id)->fresh(['estado']),
                'solicitud' => $solicitud->fresh(['estado']),
                'peso_total_acumulado' => $pesoActual,
                'peso_maximo_permitido' => $pesoMaximoPermitido,
                'parcialidades_registradas' => $parcialidadesCount,
                'parcialidades_totales' => $solicitud->cantidad_parcialidades,
                'porcentaje_completado_peso' => round(($pesoActual / $solicitud->cantidad_total) * 100, 2) . '%',
                'porcentaje_completado_parcialidades' => round(($parcialidadesCount / $solicitud->cantidad_parcialidades) * 100, 2) . '%',
                'estado_completado' => $pesoAlcanzado || $parcialidadesCompletadas
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Error al crear la parcialidad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $agricultor = Auth::user()->agricultor;
        $parcialidad = Parcialidad::whereHas('pesaje', function($query) use ($agricultor) {
            $query->whereHas('cuenta', function($q) use ($agricultor) {
                $q->where('agricultor_id', $agricultor->id);
            });
        })->where('id', $id)
          ->with(['transporte', 'transportista', 'estado', 'pesaje'])
          ->firstOrFail();
        
        return response()->json($parcialidad);
    }
}