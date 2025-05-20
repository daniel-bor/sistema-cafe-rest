<?php

namespace App\Http\Controllers;

use App\Models\Transporte;
use App\Models\Estado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransporteController extends Controller
{
    public function index()
    {
        $agricultor = Auth::user()->agricultor;
        $transportes = Transporte::where('agricultor_id', $agricultor->id)->get();
        return response()->json($transportes);
    }

    public function store(Request $request)
    {
        $request->validate([
            'placa' => 'required|string|max:10|unique:transportes,placa',
            'marca' => 'required|string|max:50',
            'color' => 'required|string|max:20',
        ]);

        $agricultor = Auth::user()->agricultor;
        if (!$agricultor) {
            return response()->json(['error' => 'Agricultor no encontrado'], 404);
        }

        $estadoActivo = Estado::where('nombre', 'Activo')
            ->where('contexto', 'transporte')
            ->first()->id;

        $transporte = Transporte::create([
            'placa' => $request->placa,
            'marca' => $request->marca,
            'color' => $request->color,
            'estado_id' => $estadoActivo,
            'disponible' => true,
            'agricultor_id' => $agricultor->id
        ]);

        return response()->json([
            'message' => 'Transporte creado con éxito',
            'transporte' => $transporte
        ], 201);
    }

    public function show($id)
    {
        $agricultor = Auth::user()->agricultor;
        $transporte = Transporte::where('id', $id)
            ->where('agricultor_id', $agricultor->id)
            ->firstOrFail();

        return response()->json($transporte);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'placa' => 'sometimes|required|string|max:10|unique:transportes,placa,' . $id,
            'marca' => 'sometimes|required|string|max:50',
            'color' => 'sometimes|required|string|max:20',
            'disponible' => 'sometimes|boolean',
        ]);

        $agricultor = Auth::user()->agricultor;
        $transporte = Transporte::where('id', $id)
            ->where('agricultor_id', $agricultor->id)
            ->firstOrFail();

        $transporte->update($request->only([
            'placa',
            'marca',
            'color',
            'disponible'
        ]));

        return response()->json([
            'message' => 'Transporte actualizado con éxito',
            'transporte' => $transporte
        ]);
    }

    public function destroy($id)
    {
        $agricultor = Auth::user()->agricultor;
        $transporte = Transporte::where('id', $id)
            ->where('agricultor_id', $agricultor->id)
            ->firstOrFail();

        $transporte->delete();

        return response()->json([
            'message' => 'Transporte eliminado con éxito'
        ]);
    }
}
