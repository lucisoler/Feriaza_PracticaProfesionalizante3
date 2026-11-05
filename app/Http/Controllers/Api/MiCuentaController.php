<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Services\FerianteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Cuenta propia del feriante (RF-04). Todas las rutas requieren un token con habilidad 'feriante'.
 */
class MiCuentaController extends Controller
{
    public function __construct(private FerianteService $ferianteService) {}

    // GET /api/v1/mi-cuenta
    public function show(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()]);
    }

    // PATCH /api/v1/mi-cuenta: solo se modifican los campos que se envían
    public function update(Request $request): JsonResponse
    {
        $feriante = $request->user();

        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:100',
            'apellido' => 'sometimes|required|string|max:100',
            'email' => ['sometimes', 'required', 'email', Rule::unique('feriantes', 'email')->ignore($feriante->id)],
            'telefono' => 'sometimes|required|string|max:20',
            'nombre_emprendimiento' => 'sometimes|nullable|string|max:150',
            'categoria_id' => 'sometimes|required|exists:categorias,id',
            'rubro_otro' => 'nullable|required_if:categoria_id,'.Categoria::idOtro().'|string|max:100',
            'instagram' => 'sometimes|nullable|string|max:100',
            'facebook' => 'sometimes|nullable|string|max:100',
            'tiktok' => 'sometimes|nullable|string|max:100',
            'password' => 'sometimes|nullable|string|min:8|confirmed',
        ]);

        $this->ferianteService->actualizar($feriante, $data);

        return response()->json(['data' => $feriante->fresh()]);
    }

    // DELETE /api/v1/mi-cuenta: baja del feriante; revoca todos sus tokens
    public function destroy(Request $request): JsonResponse
    {
        $this->ferianteService->eliminarCuentaPropia($request->user());

        return response()->json(['message' => 'Tu cuenta fue dada de baja.']);
    }
}
