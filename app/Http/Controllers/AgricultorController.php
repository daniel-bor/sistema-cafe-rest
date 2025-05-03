<?php

namespace App\Http\Controllers;

use App\Models\Agricultor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgricultorController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Agricultor::class); // Requiere policy opcional

        return Agricultor::all();
    }

    public function show($id)
    {
        $agricultor = Agricultor::find($id);
        if (!$agricultor) {
            return response()->json(['error' => 'Agricultor no encontrado'], 404);
        }

        $this->authorize('view', $agricultor);

        return response()->json($agricultor);
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'nit'         => 'required|string|unique:agricultores,nit',
            'nombre'      => 'required|string|max:100',
            'apellido'    => 'required|string|max:100',
            'telefono'    => 'nullable|string|max:20',
            'direccion'   => 'nullable|string|max:100',
            'observaciones' => 'nullable|string',
            'user_id'     => 'required|exists:users,id',
        ]);

        $agricultor = Agricultor::create($validated);

        return response()->json($agricultor, 201);
    }

    public function update(Request $request, $id)
    {
        $agricultor = Agricultor::find($id);
        if (!$agricultor) {
            return response()->json(['error' => 'Agricultor no encontrado'], 404);
        }

        if (!Auth::user()->hasRole('admin') && Auth::id() !== $agricultor->user_id) {
            return response()->json(['error' => 'No autorizado para modificar este agricultor'], 403);
        }

        $agricultor->update($request->only([
            'nombre', 'apellido', 'telefono', 'direccion', 'observaciones'
        ]));

        return response()->json($agricultor);
    }

    public function destroy($id)
    {
        $agricultor = Agricultor::find($id);
        if (!$agricultor) {
            return response()->json(['error' => 'Agricultor no encontrado'], 404);
        }

        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $agricultor->delete();
        return response()->json(['message' => 'Agricultor eliminado (soft delete)']);
    }
}
