<?php

namespace App\Http\Controllers\Api\Coordinador;

use App\Exceptions\FerianteConEventosAbiertosException;
use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Feriante;
use App\Services\FerianteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Gestión de feriantes por el coordinador (HU-05, RF-13, RF-14).
 * Todas las rutas requieren un token con habilidad 'coordinador'.
 */
class FerianteController extends Controller
{
    public function __construct(private FerianteService $ferianteService) {}

    // GET /api/v1/coordinador/feriantes?q=texto: busca por nombre, apellido, email o emprendimiento
    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $feriantes = Feriante::query()
            ->when($q !== '', function ($consulta) use ($q) {
                $consulta->where(function ($sub) use ($q) {
                    $sub->where('nombre', 'like', "%{$q}%")
                        ->orWhere('apellido', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('nombre_emprendimiento', 'like', "%{$q}%");
                });
            })
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get();

        return response()->json(['data' => $feriantes]);
    }

    // POST /api/v1/coordinador/feriantes: carga manual de un feriante
    public function store(Request $request): JsonResponse
    {
        $data = $this->validarDatos($request, null);
        $feriante = $this->ferianteService->registrar($data);

        return response()->json(['data' => $feriante->fresh()], 201);
    }

    // GET /api/v1/coordinador/feriantes/{feriante}
    public function show(Feriante $feriante): JsonResponse
    {
        return response()->json(['data' => $feriante]);
    }

    // PATCH /api/v1/coordinador/feriantes/{feriante}: solo se modifican los campos que se envían
    public function update(Request $request, Feriante $feriante): JsonResponse
    {
        $data = $this->validarDatos($request, $feriante->id, parcial: true);

        $this->ferianteService->actualizar($feriante, $data);

        return response()->json(['data' => $feriante->fresh()]);
    }

    // DELETE /api/v1/coordinador/feriantes/{feriante}: 409 si tiene inscripciones en eventos abiertos
    public function destroy(Feriante $feriante): JsonResponse
    {
        try {
            $this->ferianteService->eliminar($feriante);
        } catch (FerianteConEventosAbiertosException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json(['message' => 'Feriante eliminado.']);
    }

    // GET /api/v1/coordinador/feriantes/{feriante}/historial: participaciones, asistencia y sanciones (RF-14)
    public function historial(Feriante $feriante): JsonResponse
    {
        $inscripciones = $feriante->inscripciones()
            ->with(['evento', 'puesto', 'sancion'])
            ->latest()
            ->get();

        return response()->json([
            'feriante' => $feriante->only(['id', 'nombre', 'apellido', 'email']),
            'resumen' => [
                'participaciones' => $inscripciones->count(),
                'presentes' => $inscripciones->where('asistencia', 'presente')->count(),
                'ausentes' => $inscripciones->where('asistencia', 'ausente')->count(),
                'sanciones' => $inscripciones->whereNotNull('sancion')->count(),
            ],
            'data' => $inscripciones,
        ]);
    }

    private function validarDatos(Request $request, ?int $idFeriante, bool $parcial = false): array
    {
        // En el PATCH, 'sometimes' valida cada campo solo si se envía
        $req = $parcial ? 'sometimes|required' : 'required';

        return $request->validate([
            'nombre' => "{$req}|string|max:100",
            'apellido' => "{$req}|string|max:100",
            'email' => [...explode('|', $req), 'email', Rule::unique('feriantes', 'email')->ignore($idFeriante)],
            'password' => $idFeriante ? 'sometimes|nullable|string|min:8' : 'required|string|min:8',
            'telefono' => "{$req}|string|max:20",
            'nombre_emprendimiento' => 'sometimes|nullable|string|max:150',
            'categoria_id' => "{$req}|exists:categorias,id",
            'rubro_otro' => 'nullable|required_if:categoria_id,'.Categoria::idOtro().'|string|max:100',
            'instagram' => 'sometimes|nullable|string|max:100',
            'facebook' => 'sometimes|nullable|string|max:100',
            'tiktok' => 'sometimes|nullable|string|max:100',
            'estado' => 'sometimes|in:pendiente,aprobado,rechazado,anulado',
            // Inasistencia, falta de pago u otra falta que evaluó el coordinador (RN-18, RN-19)
            'motivo_anulacion' => 'nullable|required_if:estado,anulado|string|max:255',
        ], [
            'motivo_anulacion.required_if' => 'Indicá el motivo de la anulación.',
        ]);
    }
}
