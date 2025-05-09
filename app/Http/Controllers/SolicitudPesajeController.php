<?php

namespace App\Http\Controllers;

use App\Models\SolicitudPesaje;
use App\Models\Cuenta;
use App\Models\Pesaje;
use App\Models\Estado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SolicitudPesajeController extends Controller
{
    public function index()
    {
        $agricultor = Auth::user()->agricultor;
        $solicitudes = SolicitudPesaje::where('agricultor_id', $agricultor->id)
                                     ->with(['medidaPeso', 'estado'])
                                     ->get();
        return response()->json($solicitudes);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cantidad_total' => 'required|numeric|min:0',
            'medida_peso_id' => 'required|exists:medidas_peso,id',
            'tolerancia' => 'required|numeric|min:0|max:100',
            'precio_unitario' => 'required|numeric|min:0',
            'cantidad_parcialidades' => 'required|integer|min:1',
        ]);

        $agricultor = Auth::user()->agricultor;
        
        // Obtener los estados iniciales
        $estadoSolicitudRegistrada = Estado::where('nombre', 'Registrada')
                                    ->where('contexto', 'solicitud')
                                    ->firstOrFail()->id;
        
        $estadoCuentaRegistrada = Estado::where('nombre', 'Registrada')
                                ->where('contexto', 'cuenta')
                                ->firstOrFail()->id;
        
        $estadoPesajeSinPeso = Estado::where('nombre', 'Sin Peso')
                              ->where('contexto', 'pesaje')
                              ->firstOrFail()->id;

        DB::beginTransaction();
        
        try {
            // Crear la solicitud de pesaje
            $solicitud = SolicitudPesaje::create([
                'cantidad_total' => $request->cantidad_total,
                'medida_peso_id' => $request->medida_peso_id,
                'tolerancia' => $request->tolerancia,
                'precio_unitario' => $request->precio_unitario,
                'cantidad_parcialidades' => $request->cantidad_parcialidades,
                'estado_id' => $estadoSolicitudRegistrada,
                'agricultor_id' => $agricultor->id,
            ]);

            // Generar número de cuenta único
            $noCuenta = 'CTA-' . date('Ymd') . '-' . str_pad($solicitud->id, 5, '0', STR_PAD_LEFT);
            
            // Crear la cuenta asociada
            $cuenta = Cuenta::create([
                'no_cuenta' => $noCuenta,
                'estado_id' => $estadoCuentaRegistrada,
                'agricultor_id' => $agricultor->id,
                'solicitud_id' => $solicitud->id,
                'tolerancia' => $request->tolerancia,
            ]);
            
            // Crear registro de pesaje inicial
            $pesaje = Pesaje::create([
                'medida_peso_id' => $request->medida_peso_id,
                'peso_total' => 0, // Peso inicial 0
                'estado_id' => $estadoPesajeSinPeso,
                'solicitud_id' => $solicitud->id,
                'cuenta_id' => $cuenta->id,
                'fecha_creacion' => now(),
            ]);
            
            DB::commit();
            
            return response()->json([
                'message' => 'Solicitud de pesaje creada con éxito',
                'solicitud' => $solicitud,
                'cuenta' => $cuenta,
                'pesaje' => $pesaje
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Error al crear la solicitud de pesaje',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $agricultor = Auth::user()->agricultor;
        $solicitud = SolicitudPesaje::where('id', $id)
                                    ->where('agricultor_id', $agricultor->id)
                                    ->with(['cuenta', 'pesaje', 'medidaPeso', 'estado'])
                                    ->firstOrFail();
        
        return response()->json($solicitud);
    }
}

