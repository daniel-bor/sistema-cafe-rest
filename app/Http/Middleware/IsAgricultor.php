<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class IsAgricultor
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            // Verifica que el usuario tenga rol de agricultor (id 1)
            if (!$user) {
                return response()->json([
                    'message' => 'Usuario no encontrado'
                ], 401);
            }
            
            if ($user->rol_id != 1) {
                return response()->json([
                    'message' => 'Acceso no autorizado. Se requiere rol de Agricultor.'
                ], 403);
            }
            
        } catch (TokenExpiredException $e) {
            return response()->json([
                'message' => 'La sesión ha expirado. Por favor, inicie sesión nuevamente.'
            ], 401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'message' => 'Token de autenticación inválido. Por favor, inicie sesión nuevamente.'
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Token no proporcionado o sesión finalizada'
            ], 401);
        }

        return $next($request);
    }
}