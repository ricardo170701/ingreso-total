<?php

namespace App\Http\Controllers;

use App\Models\Secretaria;
use App\Models\Piso;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DependenciasController extends Controller
{
    /**
     * Listar secretarías (las secretarías son el recurso principal)
     * "Dependencias" es solo el nombre del módulo
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Secretaria::class);

        $perPage = (int) ($request->query('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        $secretarias = Secretaria::query()
            ->with('piso')
            ->withCount('gerencias')
            ->orderBy('nombre')
            ->paginate($perPage);

        return Inertia::render('Dependencias/Index', [
            'secretarias' => $secretarias,
        ]);
    }
}