<?php

namespace App\Http\Controllers;

use App\Models\Transportista;
use App\Models\Estado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TransportistaController extends Controller
{
    public function index()
    {
        $agricultor = Auth::user()->agricultor;
        $transportistas = Transportista::where('agricultor_id', $agricultor->id)->get();
        return response()->json($transportistas);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cui' => 'required|string|max:20|unique:transportistas,cui',
            'nombre_completo' => 'required|string|max:100',
            'fecha_nacimiento' => 'required|date',
            'tipo_licencia' => 'required|string|max:20',
            'fecha_vencimiento_licencia' => 'required|date',
            'foto' => 'nullable|image|max:2048',
        ]);

        $agricultor = Auth::user()->agricultor;
        $estadoActivo = Estado::where('nombre', 'Activo')
                              ->where('contexto', 'transportista')
                              ->first()->id;

        $transportistaData = [
            'cui' => $request->cui,
            'nombre_completo' => $request->nombre_completo,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'tipo_licencia' => $request->tipo_licencia,
            'fecha_vencimiento_licencia' => $request->fecha_vencimiento_licencia,
            'agricultor_id' => $agricultor->id,
            'estado_id' => $estadoActivo,
            'disponible' => true,
        ];

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('transportistas', 'public');
            $transportistaData['foto'] = $path;
        }

        $transportista = Transportista::create($transportistaData);

        return response()->json([
            'message' => 'Transportista creado con éxito',
            'transportista' => $transportista
        ], 201);
    }

    public function show($id)
    {
        $agricultor = Auth::user()->agricultor;
        $transportista = Transportista::where('id', $id)
                                     ->where('agricultor_id', $agricultor->id)
                                     ->firstOrFail();
        
        return response()->json($transportista);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'cui' => 'sometimes|required|string|max:20|unique:transportistas,cui,'.$id,
            'nombre_completo' => 'sometimes|required|string|max:100',
            'fecha_nacimiento' => 'sometimes|required|date',
            'tipo_licencia' => 'sometimes|required|string|max:20',
            'fecha_vencimiento_licencia' => 'sometimes|required|date',
            'disponible' => 'sometimes|boolean',
            'foto' => 'nullable|image|max:2048',
        ]);

        $agricultor = Auth::user()->agricultor;
        $transportista = Transportista::where('id', $id)
                                     ->where('agricultor_id', $agricultor->id)
                                     ->firstOrFail();

        $transportistaData = $request->only([
            'cui', 'nombre_completo', 'fecha_nacimiento', 
            'tipo_licencia', 'fecha_vencimiento_licencia', 'disponible'
        ]);

        if ($request->hasFile('foto')) {
            // Eliminar foto anterior si existe
            if ($transportista->foto) {
                Storage::disk('public')->delete($transportista->foto);
            }
            
            $path = $request->file('foto')->store('transportistas', 'public');
            $transportistaData['foto'] = $path;
        }

        $transportista->update($transportistaData);

        return response()->json([
            'message' => 'Transportista actualizado con éxito',
            'transportista' => $transportista
        ]);
    }

    public function destroy($id)
    {
        $agricultor = Auth::user()->agricultor;
        $transportista = Transportista::where('id', $id)
                                     ->where('agricultor_id', $agricultor->id)
                                     ->firstOrFail();
        
        if ($transportista->foto) {
            Storage::disk('public')->delete($transportista->foto);
        }
        
        $transportista->delete();
        
        return response()->json([
            'message' => 'Transportista eliminado con éxito'
        ]);
    }
}