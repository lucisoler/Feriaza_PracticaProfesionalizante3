<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Lugar;
use App\Models\Puesto;
use App\Models\Seccion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Diseño de la feria sobre el plano digitalizado de cada lugar (RF-19):
 * secciones por categoría, zonas no disponibles y mesas de cada sección.
 */
class PlanoController extends Controller
{
    public function show(Request $request): Response
    {
        $lugares = Lugar::orderBy('id')->get(['id', 'nombre', 'slug']);
        $lugar = Lugar::where('slug', $request->query('lugar'))->first() ?? Lugar::orderBy('id')->firstOrFail();

        return Inertia::render('Coordinador/Plano', [
            'lugares' => $lugares,
            'lugar' => $lugar->conPlano(),
        ]);
    }

    // ---------- Secciones ----------

    public function storeSeccion(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'lugar_id' => 'required|exists:lugares,id',
            ...$this->reglasSeccion(),
        ]);

        $this->validarDentroDelPlano(Lugar::findOrFail($data['lugar_id']), $data);
        Seccion::create($data);

        return back()->with('success', 'Sección creada.');
    }

    public function updateSeccion(Request $request, Seccion $seccion): RedirectResponse
    {
        $data = $request->validate($this->reglasSeccion(parcial: true));

        $this->validarDentroDelPlano($seccion->lugar, array_merge($seccion->only(['x', 'y', 'ancho', 'alto']), $data));
        $seccion->update($data);

        return back()->with('success', 'Sección actualizada.');
    }

    public function destroySeccion(Seccion $seccion): RedirectResponse
    {
        if ($seccion->puestos->contains(fn (Puesto $p) => $p->tieneInscripcionesVigentes())) {
            return back()->withErrors(['seccion' => 'Hay mesas de esta sección con inscripciones en eventos abiertos.']);
        }

        $seccion->puestos()->delete();
        $seccion->delete();

        return back()->with('success', 'Sección eliminada junto con sus mesas.');
    }

    // Distribuye N mesas nuevas en grilla dentro de la sección, sin pisar las que ya están
    public function completarSeccion(Request $request, Seccion $seccion): RedirectResponse
    {
        $data = $request->validate(['cantidad' => 'required|integer|min:1|max:200']);

        if (! $seccion->disponible) {
            return back()->withErrors(['seccion' => 'La sección está marcada como no disponible.']);
        }

        $ocupadas = $seccion->lugar->puestos->map->only(['x', 'y'])->all();
        $posiciones = $seccion->posicionesLibres($data['cantidad'], $ocupadas);

        foreach ($posiciones as $punto) {
            Puesto::create([
                'lugar_id' => $seccion->lugar_id,
                'seccion_id' => $seccion->id,
                'numero' => $seccion->lugar->siguienteNumero(),
                'estado' => 'libre',
            ] + $punto);
        }

        $agregadas = count($posiciones);
        $mensaje = $agregadas === $data['cantidad']
            ? "Se agregaron {$agregadas} mesas."
            : "Solo entraron {$agregadas} mesas en el espacio libre de la sección.";

        return back()->with('success', $mensaje);
    }

    // ---------- Mesas ----------

    public function storePuesto(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'lugar_id' => 'required|exists:lugares,id',
            'x' => 'required|integer|min:0',
            'y' => 'required|integer|min:0',
        ]);

        $lugar = Lugar::findOrFail($data['lugar_id']);
        $seccion = $this->seccionDisponibleEn($lugar, $data['x'], $data['y']);

        Puesto::create([
            'lugar_id' => $lugar->id,
            'seccion_id' => $seccion->id,
            'numero' => $lugar->siguienteNumero(),
            'estado' => 'libre',
            'x' => $data['x'],
            'y' => $data['y'],
        ]);

        return back();
    }

    // Mover la mesa (x, y) o habilitarla / retirarla del plano
    public function updatePuesto(Request $request, Puesto $puesto): RedirectResponse
    {
        $data = $request->validate([
            'x' => 'sometimes|integer|min:0',
            'y' => 'sometimes|integer|min:0',
            'habilitado' => 'sometimes|boolean',
        ]);

        if (isset($data['x'], $data['y'])) {
            // Al moverla, queda en la sección donde se soltó
            $data['seccion_id'] = $this->seccionDisponibleEn($puesto->lugar, $data['x'], $data['y'])->id;
        }

        $puesto->update($data);

        return back();
    }

    public function destroyPuesto(Puesto $puesto): RedirectResponse
    {
        if ($puesto->tieneInscripcionesVigentes()) {
            return back()->withErrors(['puesto' => "La mesa {$puesto->numero} tiene inscripciones en eventos abiertos."]);
        }

        $puesto->delete();

        return back();
    }

    // ---------- Colores de las categorías ----------

    public function updateCategoria(Request $request, Categoria $categoria): RedirectResponse
    {
        $data = $request->validate(['color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/']]);
        $categoria->update($data);

        return back();
    }

    private function reglasSeccion(bool $parcial = false): array
    {
        $req = $parcial ? 'sometimes' : 'required';

        return [
            'nombre' => "{$req}|string|max:60",
            'categoria_id' => 'nullable|exists:categorias,id',
            'disponible' => "{$req}|boolean",
            'x' => "{$req}|integer|min:0",
            'y' => "{$req}|integer|min:0",
            'ancho' => "{$req}|integer|min:20",
            'alto' => "{$req}|integer|min:20",
        ];
    }

    private function validarDentroDelPlano(Lugar $lugar, array $rect): void
    {
        if ($rect['x'] + $rect['ancho'] > $lugar->ancho || $rect['y'] + $rect['alto'] > $lugar->alto) {
            throw ValidationException::withMessages(['seccion' => 'La sección se sale del plano.']);
        }
    }

    private function seccionDisponibleEn(Lugar $lugar, int $x, int $y): Seccion
    {
        $seccion = $lugar->seccionEn($x, $y);

        if (! $seccion) {
            throw ValidationException::withMessages(['puesto' => 'Las mesas van dentro de una sección del plano.']);
        }
        if (! $seccion->disponible) {
            throw ValidationException::withMessages(['puesto' => "«{$seccion->nombre}» es una zona no disponible."]);
        }

        return $seccion;
    }
}
