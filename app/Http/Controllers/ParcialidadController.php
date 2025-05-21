<?php

namespace App\Http\Controllers;

use App\Models\Parcialidad;
use App\Models\Pesaje;
use App\Models\Transporte;
use App\Models\Transportista;
use App\Models\Estado;
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
            $query->where('agricultor_id', $agricultor->id);
        })->with(['pesaje', 'transporte', 'transportista', 'estado'])->get();
        
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
                        ->where('agricultor_id', $agricultor->id)
                        ->with(['medidaPeso'])
                        ->first();
        
        if (!$pesaje) {
            return response()->json(['message' => 'El pesaje no existe o no pertenece a este agricultor'], 404);
        }
        
        // Verificar que el estado del pesaje permita añadir parcialidades
        $estadosPermitidos = ['Pendiente', 'Aprobado', 'En Proceso'];
        
        if (!in_array($pesaje->estado->nombre, $estadosPermitidos)) {
            return response()->json([
                'message' => 'No se pueden agregar parcialidades en el estado actual del pesaje',
                'estado_actual' => $pesaje->estado->nombre,
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
        
        // Verificar que el transportista pertenezca al agricultor
        $transportista = Transportista::where('id', $request->transportista_id)
                                    ->where('agricultor_id', $agricultor->id)
                                    ->first();
        
        if (!$transportista) {
            return response()->json(['message' => 'El transportista no existe o no pertenece a este agricultor'], 404);
        }
        
        // Verificar cantidad de parcialidades
        $parcialidadesActuales = Parcialidad::where('pesaje_id', $pesaje->id)->count();
        
        if ($parcialidadesActuales >= $pesaje->cantidad_parcialidades) {
            return response()->json([
                'message' => 'No se puede agregar más parcialidades. Ya se alcanzó el límite especificado',
                'parcialidades_actuales' => $parcialidadesActuales,
                'parcialidades_permitidas' => $pesaje->cantidad_parcialidades
            ], 400);
        }
        
        // Verificar tipo de medida
        if ($pesaje->medidaPeso->nombre != $request->tipo_medida) {
            return response()->json([
                'message' => 'La medida de la parcialidad debe coincidir con la medida del pesaje',
                'medida_pesaje' => $pesaje->medidaPeso->nombre,
                'medida_solicitada' => $request->tipo_medida
            ], 400);
        }
        
        // Verificar límite de peso
        $pesoFuturo = $pesaje->peso_total + $request->peso;
        $pesoMaximoPermitido = $pesaje->cantidad_total * (1 + ($pesaje->tolerancia / 100));
        
        if ($pesoFuturo > $pesoMaximoPermitido) {
            return response()->json([
                'message' => 'No se puede agregar esta parcialidad porque excedería el peso total permitido',
                'peso_actual' => $pesaje->peso_total,
                'peso_a_agregar' => $request->peso,
                'peso_resultante' => $pesoFuturo,
                'peso_maximo' => $pesoMaximoPermitido,
                'cantidad_total' => $pesaje->cantidad_total,
                'tolerancia' => $pesaje->tolerancia . '%'
            ], 400);
        }
        
        // Obtener estado pendiente para parcialidad
        $estadoPendiente = Estado::where('nombre', 'Pendiente')
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
            
            DB::commit();
            
            return response()->json([
                'message' => 'Parcialidad creada correctamente. Pendiente de aprobación.',
                'parcialidad' => $parcialidad,
                'pesaje' => $pesaje
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
            $query->where('agricultor_id', $agricultor->id);
        })->where('id', $id)
          ->with(['pesaje', 'transporte', 'transportista', 'estado'])
          ->firstOrFail();
        
        return response()->json($parcialidad);
    }
}