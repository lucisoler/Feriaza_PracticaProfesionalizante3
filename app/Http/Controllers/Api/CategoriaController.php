<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\JsonResponse;

class CategoriaController extends Controller
{
    // GET /api/v1/categorias: pública, para el formulario de registro (RF-02)
    public function index(): JsonResponse
    {
        $categorias = Categoria::orderBy('prioridad')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'prioridad', 'recibe_mesa']);

        return response()->json(['data' => $categorias]);
    }
}
