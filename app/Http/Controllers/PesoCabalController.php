<?php

namespace App\Http\Controllers;

use App\Models\PesoCabal;
use App\Models\Parcialidad;
use App\Models\Pesaje;
use App\Models\Cuenta;
use App\Models\Boleta;
use App\Models\Estado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PesoCabalController extends Controller
{
    /**
     * Perfil del peso cabal
     */
    public function perfil()
    {
        $pesoCabal = PesoCabal::where('user_id', Auth::id())->first();

        if (!$pesoCabal) {
            return response()->json(['error' => 'Perfil de Peso Cabal no encontrado'], 404);
        }

        return response()->json($pesoCabal);
    }

    /**
     * Listar parcialidades pendientes de verificación (ya aprobadas por beneficio)
     */
    public function parcialidadesPorVerificar()
    {
        $estadoAprobada = Estado::where('nombre', 'Aprobada')
                             ->where('contexto', 'parcialidad')
                             ->firstOrFail()->id;
                                
        $parcialidades = Parcialidad::where('estado_id', $estadoAprobada)
                                   ->whereNull('fecha_peso') // Sin peso registrado aún
                                   ->with(['pesaje.agricultor', 'transporte', 'transportista', 'estado'])
                                   ->get();
                                     
        return response()->json($parcialidades);
    }

    /**
     * Registrar peso de una parcialidad
     */
    public function registrarPeso(Request $request, $id)
    {
        $request->validate([
            'peso_bascula' => 'required|numeric|min:0',
            'observaciones_peso' => 'nullable|string',
        ]);

        $pesoCabal = PesoCabal::where('user_id', Auth::id())->firstOrFail();
        
        DB::beginTransaction();
        
        try {
            // Obtener estados necesarios
            $estadoParcialidadVerificada = Estado::where('nombre', 'Verificada')
                                          ->where('contexto', 'parcialidad')
                                          ->firstOrFail()->id;
            
            $estadoPesajeIniciado = Estado::where('nombre', 'Iniciado')
                                      ->where('contexto', 'pesaje')
                                      ->firstOrFail()->id;
            
            $estadoPesajeFinalizado = Estado::where('nombre', 'Finalizado')
                                        ->where('contexto', 'pesaje')
                                        ->firstOrFail()->id;
            
            $estadoCuentaAbierta = Estado::where('nombre', 'Abierta')
                                    ->where('contexto', 'cuenta')
                                    ->firstOrFail()->id;
            
            $estadoCuentaCerrada = Estado::where('nombre', 'Cerrada')
                                    ->where('contexto', 'cuenta')
                                    ->firstOrFail()->id;
            
            // Buscar la parcialidad
            $parcialidad = Parcialidad::with('pesaje')->findOrFail($id);
            $pesaje = $parcialidad->pesaje;
            
            // Verificar que la parcialidad esté en estado Aprobada
            $estadoAprobada = Estado::where('nombre', 'Aprobada')
                              ->where('contexto', 'parcialidad')
                              ->firstOrFail()->id;
                              
            if ($parcialidad->estado_id != $estadoAprobada) {
                return response()->json([
                    'message' => 'Solo se pueden registrar pesos en parcialidades aprobadas',
                    'estado_actual' => $parcialidad->estado->nombre
                ], 400);
            }
            
            // Actualizar la parcialidad
            $parcialidad->peso_bascula = $request->peso_bascula;
            $parcialidad->observaciones_peso = $request->observaciones_peso;
            $parcialidad->fecha_peso = now();
            $parcialidad->verificada_por = $pesoCabal->id;
            $parcialidad->estado_id = $estadoParcialidadVerificada;
            $parcialidad->save();
            
            // Obtener cuenta
            $cuenta = Cuenta::findOrFail($pesaje->cuenta_id);
            
            // Verificar si es la primera parcialidad verificada
            $parcialidadesVerificadas = $pesaje->parcialidades()
                                      ->where('estado_id', $estadoParcialidadVerificada)
                                      ->count();
            
            if ($parcialidadesVerificadas == 1) {
                // Es la primera parcialidad verificada
                $pesaje->estado_id = $estadoPesajeIniciado;
                $pesaje->fecha_inicio = now();
                $pesaje->save();
                
                // Actualizar estado de la cuenta
                $cuenta->estado_id = $estadoCuentaAbierta;
                $cuenta->save();
            }
            
            // Verificar si todas las parcialidades han sido verificadas
            $totalParcialidades = $pesaje->cantidad_parcialidades;
            
            if ($parcialidadesVerificadas >= $totalParcialidades) {
                // Todas las parcialidades han sido verificadas
                $pesaje->estado_id = $estadoPesajeFinalizado;
                $pesaje->fecha_cierre = now();
                $pesaje->save();
                
                // Actualizar estado de la cuenta
                $cuenta->estado_id = $estadoCuentaCerrada;
                $cuenta->save();
            }
            
            DB::commit();
            
            return response()->json([
                'message' => 'Peso registrado correctamente',
                'parcialidad' => $parcialidad,
                'pesaje' => $pesaje,
                'cuenta' => $cuenta
            ], 200);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Error al registrar el peso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar boleta para una parcialidad
     */
    public function generarBoleta(Request $request, $id)
    {
        $pesoCabal = PesoCabal::where('user_id', Auth::id())->firstOrFail();
        
        // Buscar parcialidad y verificar que esté verificada
        $parcialidad = Parcialidad::with([
            'pesaje.cuenta', 
            'pesaje.agricultor', 
            'transporte', 
            'transportista'
        ])->findOrFail($id);
        
        $estadoVerificada = Estado::where('nombre', 'Verificada')
                          ->where('contexto', 'parcialidad')
                          ->firstOrFail()->id;
        
        if ($parcialidad->estado_id != $estadoVerificada) {
            return response()->json([
                'message' => 'Solo se pueden generar boletas para parcialidades verificadas',
                'estado_actual' => $parcialidad->estado->nombre
            ], 400);
        }
        
        // Verificar si ya existe una boleta para esta parcialidad
        $boletaExistente = Boleta::where('parcialidad_id', $parcialidad->id)->first();
        
        if ($boletaExistente) {
            return response()->json([
                'message' => 'Ya existe una boleta para esta parcialidad',
                'boleta' => $boletaExistente
            ], 400);
        }
        
        // Generar número de boleta único
        $noBoleta = 'BOL-' . date('Ymd') . '-' . str_pad($parcialidad->id, 5, '0', STR_PAD_LEFT);
        
        // Crear boleta
        $boleta = Boleta::create([
            'no_boleta' => $noBoleta,
            'fecha_boleta' => now(),
            'parcialidad_id' => $parcialidad->id,
            'generada_por' => $pesoCabal->id,
            'observaciones' => $request->observaciones ?? null
        ]);
        
        // Preparar datos para la respuesta
        $datosCompletos = [
            'boleta' => $boleta,
            'fecha_boleta' => $boleta->fecha_boleta->format('Y-m-d H:i:s'),
            'usuario_generador' => $pesoCabal->nombre,
            'cuenta' => $parcialidad->pesaje->cuenta->no_cuenta,
            'id_pesaje' => $parcialidad->pesaje->id,
            'id_parcialidad' => $parcialidad->id,
            'placa_transporte' => $parcialidad->transporte->placa,
            'cui_transportista' => $parcialidad->transportista->cui,
            'tipo_medida' => $parcialidad->tipo_medida,
            'peso_obtenido' => $parcialidad->peso_bascula,
            'fecha_pesaje' => $parcialidad->fecha_peso,
            'agricultor' => $parcialidad->pesaje->agricultor->nombre . ' ' . $parcialidad->pesaje->agricultor->apellido,
        ];
        
        return response()->json([
            'message' => 'Boleta generada correctamente',
            'datos' => $datosCompletos
        ], 201);
    }

    /**
     * Obtener detalle de una boleta
     */
    public function verBoleta($id)
    {
        $boleta = Boleta::with([
            'parcialidad.pesaje.cuenta',
            'parcialidad.pesaje.agricultor',
            'parcialidad.transporte',
            'parcialidad.transportista',
            'pesoCabal'
        ])->findOrFail($id);
        
        // Preparar datos para la respuesta
        $datosCompletos = [
            'boleta' => $boleta,
            'fecha_boleta' => $boleta->fecha_boleta->format('Y-m-d H:i:s'),
            'usuario_generador' => $boleta->pesoCabal->nombre,
            'cuenta' => $boleta->parcialidad->pesaje->cuenta->no_cuenta,
            'id_pesaje' => $boleta->parcialidad->pesaje->id,
            'id_parcialidad' => $boleta->parcialidad->id,
            'placa_transporte' => $boleta->parcialidad->transporte->placa,
            'cui_transportista' => $boleta->parcialidad->transportista->cui,
            'tipo_medida' => $boleta->parcialidad->tipo_medida,
            'peso_obtenido' => $boleta->parcialidad->peso_bascula,
            'fecha_pesaje' => $boleta->parcialidad->fecha_peso,
            'agricultor' => $boleta->parcialidad->pesaje->agricultor->nombre . ' ' . $boleta->parcialidad->pesaje->agricultor->apellido,
        ];
        
        return response()->json($datosCompletos);
    }

    /**
     * Listar boletas generadas
     */
    public function listarBoletas()
    {
        $pesoCabal = PesoCabal::where('user_id', Auth::id())->firstOrFail();
        
        $boletas = Boleta::where('generada_por', $pesoCabal->id)
                        ->with('parcialidad')
                        ->orderBy('fecha_boleta', 'desc')
                        ->get();
        
        return response()->json($boletas);
    }
}