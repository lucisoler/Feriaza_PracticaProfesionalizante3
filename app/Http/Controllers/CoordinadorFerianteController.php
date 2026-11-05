<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Feriante;
use App\Notifications\CuentaAnulada;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class CoordinadorFerianteController extends Controller
{
    public function create(): Response
    {

        return Inertia::render('Coordinador/FerianteForm', [
            'feriante' => null,
        ]);

    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validarDatos($request, null);

        Feriante::create($data);

        return redirect('/coordinador')->with('success', 'Feriante creado.');
    }

    public function edit(int $id): Response
    {

        $feriante = Feriante::findOrFail($id);

        return Inertia::render('Coordinador/FerianteForm', [
            'feriante' => $feriante,
        ]);

    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $feriante = Feriante::findOrFail($id);
        $data = $this->validarDatos($request, $feriante->id);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $feriante->update($data);

        return redirect('/coordinador')->with('success', 'Feriante actualizado.');

    }

    public function cambiarEstado(Request $request, int $id): RedirectResponse
    {

        $data = $request->validate([
            'estado' => 'required|in:pendiente,aprobado,rechazado,anulado',
            // Inasistencia, falta de pago u otra falta que evaluó el coordinador (RN-18, RN-19)
            'motivo_anulacion' => 'nullable|required_if:estado,anulado|string|max:255',
        ], [
            'motivo_anulacion.required_if' => 'Indicá el motivo de la anulación.',
        ]);

        $feriante = Feriante::findOrFail($id);

        if ($data['estado'] !== 'anulado') {
            $data['motivo_anulacion'] = null;
        }

        $feriante->update($data);

        if ($feriante->estaAnulado()) {
            // Una cuenta anulada no conserva mesas: se liberan sus reservas de hoy en adelante
            $feriante->inscripciones()->whereDate('fecha', '>=', today())->delete();

            try {
                $feriante->notify(new CuentaAnulada($feriante));
            } catch (Throwable $e) {
                report($e);
            }
        }

        return back();

    }

    public function destroy(int $id): RedirectResponse
    {

        $feriante = Feriante::findOrFail($id);

        $feriante->delete();

        return back()->with('success', 'Feriante eliminado.');

    }

    private function validarDatos(Request $request, ?int $idFeriante): array
    {

        return $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => [
                'required', 'email',
                Rule::Unique('feriantes', 'email')->ignore($idFeriante),

            ],
            'password' => $idFeriante ? 'nullable|string|min:8' : 'required|string|min:8',
            'nombre_emprendimiento' => 'nullable|string|max:150',
            'categoria_id' => 'required|exists:categorias,id',
            'rubro_otro' => 'nullable|required_if:categoria_id,'.Categoria::idOtro().'|string|max:100',
            'telefono' => 'required|string|max:20',
            'instagram' => 'nullable|string|max:100',
            'facebook' => 'nullable|string|max:100',
            'tiktok' => 'nullable|string|max:100',
            'consulta' => 'nullable|string',
            'estado' => 'nullable|in:pendiente,aprobado,rechazado,anulado',

        ]);

    }
}
