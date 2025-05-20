<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Agricultor;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            // Campos opcionales para agricultor
            'nit'      => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string',
        ]);
    
        // Corregir 'rol-id' a 'rol_id'
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'rol_id'   => $request->rol_id ?? 1, // Asignar rol de agricultor por defecto
        ]);
    
        // Si el usuario es un agricultor (rol_id = 1), crear registro en tabla agricultores
        if ($user->rol_id == 1) {
            // Extraer nombre y apellido del name (o usar valores predeterminados)
            $nombreCompleto = explode(' ', $user->name, 2);
            $nombre = $nombreCompleto[0];
            $apellido = $nombreCompleto[1] ?? '';
    
            Agricultor::create([
                'nit'          => $request->nit ?? 'Pendiente',
                'nombre'       => $nombre,
                'apellido'     => $apellido,
                'telefono'     => $request->telefono,
                'direccion'    => $request->direccion,
                'observaciones' => $request->observaciones,
                'user_id'      => $user->id,
            ]);
        }
    
        try {
            $token = JWTAuth::fromUser($user);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
    
        return response()->json([
            'token' => $token,
            'user'  => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }

        return response()->json([
            'token'      => $token,
            'expires_in' => JWTauth::factory()->getTTL() * 60,
        ]);
    }
    public function logout()
    {
        try {
            // Verificar si hay un token antes de intentar invalidarlo
            $token = JWTAuth::getToken();
            if (!$token) {
                return response()->json(['message' => 'Ya ha cerrado sesión'], 200);
            }
            
            JWTAuth::invalidate($token);
            return response()->json(['message' => 'Sesión cerrada con éxito']);
        } catch (TokenExpiredException $e) {
            // Token expirado
            return response()->json(['message' => 'Token expirado, ya ha cerrado sesión'], 200);
        } catch (TokenInvalidException $e) {
            // Token inválido
            return response()->json(['message' => 'Token inválido, ya ha cerrado sesión'], 200);
        } catch (JWTException $e) {
            // Registra el error para depuración
            Log::error('Error en logout JWT: ' . $e->getMessage());
            return response()->json(['message' => 'Cierre de sesión procesado'], 200);
        }
    }

    

    public function getUser()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
            return response()->json($user);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Failed to fetch user profile'], 500);
        }
    }

    public function updateUser(Request $request)
    {
        try {
            $user = Auth::user();
            $user->update($request->only(['name', 'email']));
            return response()->json($user);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Failed to update user'], 500);
        }
    }
}
