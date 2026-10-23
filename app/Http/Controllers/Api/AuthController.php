<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Feriante;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Autenticación de la API con Sanctum (docs/diseno-api.md, sección 2).
 * Cada token lleva una habilidad que identifica el rol: 'feriante' o 'coordinador'.
 */
class AuthController extends Controller
{
    // POST /api/v1/feriantes/registro (RF-01, RF-02)
    public function registro(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|email|unique:feriantes,email',
            'password' => 'required|string|min:8|confirmed',
            'telefono' => 'required|string|max:20',
            'nombre_emprendimiento' => 'nullable|string|max:150',
            'categoria_id' => 'required|exists:categorias,id',
            'rubro_otro' => 'nullable|required_if:categoria_id,' . Categoria::idOtro() . '|string|max:100',
            'instagram' => 'nullable|string|max:100',
            'facebook' => 'nullable|string|max:100',
            'tiktok' => 'nullable|string|max:100',
        ]);

        // La contraseña se hashea sola por el cast 'hashed' del modelo
        $feriante = Feriante::create($data);
        $token = $feriante->createToken('feriante', ['feriante'])->plainTextToken;

        return response()->json([
            'feriante' => $feriante,
            'token' => $token,
        ], 201);
    }

    // POST /api/v1/feriantes/login (RF-03)
    public function loginFeriante(Request $request): JsonResponse
    {
        $creds = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $feriante = Feriante::where('email', $creds['email'])->first();

        if (! $feriante || ! Hash::check($creds['password'], $feriante->password)) {
            throw ValidationException::withMessages([
                'email' => 'El email o la contraseña no son correctos.',
            ]);
        }

        return response()->json([
            'feriante' => $feriante,
            'token' => $feriante->createToken('feriante', ['feriante'])->plainTextToken,
        ]);
    }

    // POST /api/v1/coordinador/login (RF-03)
    public function loginCoordinador(Request $request): JsonResponse
    {
        $creds = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $coordinador = User::where('email', $creds['email'])->first();

        if (! $coordinador || ! Hash::check($creds['password'], $coordinador->password)) {
            throw ValidationException::withMessages([
                'email' => 'El email o la contraseña no son correctos.',
            ]);
        }

        return response()->json([
            'coordinador' => $coordinador->only(['id', 'name', 'email']),
            'token' => $coordinador->createToken('coordinador', ['coordinador'])->plainTextToken,
        ]);
    }

    // POST /api/v1/logout: revoca el token con el que se hizo el pedido (feriante o coordinador)
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada.']);
    }
}
