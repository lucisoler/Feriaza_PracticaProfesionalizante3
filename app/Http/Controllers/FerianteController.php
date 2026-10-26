<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Feriante;
use App\Models\Puesto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FerianteController extends Controller
{
    // CREATE público: formulario de inscripción en 3 pasos (POST /feriantes)
    public function store(Request $request): RedirectResponse
    {
        // Solo las categorías que reciben mesa tienen que elegir puesto (RN-03)
        $categoria = Categoria::find($request->input('categoria_id'));
        $recibeMesa = $categoria?->recibe_mesa ?? true;

        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|email|unique:feriantes,email',
            'password' => 'required|string|min:8|confirmed',
            'nombre_emprendimiento' => 'nullable|string|max:150',
            'categoria_id' => 'required|exists:categorias,id',
            'rubro_otro' => 'nullable|required_if:categoria_id,' . Categoria::idOtro() . '|string|max:100',
            'telefono' => 'required|string|max:20',
            'instagram' => 'nullable|string|max:100',
            'facebook' => 'nullable|string|max:100',
            'tiktok' => 'nullable|string|max:100',
            'consulta' => 'nullable|string',
            'asistencia_confirmada' => 'required|accepted',
            'puesto_id' => [
                Rule::requiredIf($recibeMesa),
                'nullable',
                'exists:puestos,id',
            ],
        ], [
            'puesto_id.required' => 'Elegí una mesa del plano.',
        ]);

        // Los revendedores no reciben mesa: aunque llegue un puesto, se ignora
        $puestoId = $recibeMesa ? $data['puesto_id'] : null;
        unset($data['puesto_id']);

        // Todo o nada: si algo falla, no queda el feriante guardado sin su mesa
        [$feriante, $puesto] = DB::transaction(function () use ($data, $puestoId) {
            $puesto = null;

            if ($puestoId) {
                // lockForUpdate evita que dos personas reserven la misma mesa a la vez (RN-09)
                $puesto = Puesto::lockForUpdate()->findOrFail($puestoId);

                if ($puesto->estado === 'reservado') {
                    throw ValidationException::withMessages([
                        'puesto_id' => 'Esa mesa ya fue reservada. Elegí otra.',
                    ]);
                }
            }

            $feriante = Feriante::create($data);

            $puesto?->update([
                'estado' => 'reservado',
                'feriante_id' => $feriante->id,
            ]);

            return [$feriante, $puesto];
        });

        // Queda con la sesión iniciada para entrar directo a "Mi cuenta"
        Auth::guard('feriante')->login($feriante);

        $numero = config('feria.whatsapp_coordinador');
        $mesa = $puesto ? "Mesa N° {$puesto->numero}" : 'Sin mesa (trae la suya)';
        $msg = urlencode(
            "Nuevo feriante:\n"
            . "{$feriante->nombre} {$feriante->apellido}\n"
            . "Categoría: {$feriante->categoria->nombre}\n"
            . "{$mesa}\n"
            . "Tel: {$feriante->telefono}"
        );
        $link = "https://wa.me/{$numero}?text={$msg}";

        return redirect('/gracias')->with('whatsapp', $link);
    }
}
