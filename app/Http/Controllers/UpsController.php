<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUpsRequest;
use App\Http\Requests\UpdateUpsRequest;
use App\Models\Piso;
use App\Models\Ups;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class UpsController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Ups::class);

        $perPage = (int) ($request->query('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        $pisoId = $request->query('piso_id');
        $q = trim((string) $request->query('q', ''));

        $query = Ups::query()->with('piso');

        if ($pisoId) {
            $query->where('piso_id', $pisoId);
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('codigo', 'like', "%{$q}%")
                    ->orWhere('nombre', 'like', "%{$q}%")
                    ->orWhere('marca', 'like', "%{$q}%")
                    ->orWhere('modelo', 'like', "%{$q}%")
                    ->orWhere('serial', 'like', "%{$q}%");
            });
        }

        $ups = $query->orderBy('nombre')->paginate($perPage)->withQueryString();

        $pisos = Piso::query()->orderBy('nombre')->get();

        return Inertia::render('Ups/Index', [
            'ups' => $ups,
            'pisos' => $pisos,
            'filtros' => [
                'piso_id' => $pisoId ? (int) $pisoId : null,
                'q' => $q,
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Ups::class);

        return Inertia::render('Ups/Create', [
            'pisos' => Piso::query()->orderBy('nombre')->get(),
        ]);
    }

    public function store(StoreUpsRequest $request)
    {
        $this->authorize('create', Ups::class);

        $data = $request->validated();
        $data['activo'] = (bool) ($data['activo'] ?? true);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('ups/fotos', 'public');
        }

        Ups::create($data);

        return redirect()
            ->route('ups.index')
            ->with('message', 'UPS registrada exitosamente.');
    }

    public function show(Ups $ups): Response
    {
        $this->authorize('view', $ups);

        $ups->load([
            'piso',
            'mantenimientos.creadoPor',
            'mantenimientos.editadoPor',
            'mantenimientos.documentos',
            'mantenimientos.imagenes',
        ]);

        return Inertia::render('Ups/Show', [
            'ups' => $ups,
        ]);
    }

    public function edit(Ups $ups): Response
    {
        $this->authorize('update', $ups);

        $ups->load('piso');

        return Inertia::render('Ups/Edit', [
            'ups' => $ups,
            'pisos' => Piso::query()->orderBy('nombre')->get(),
        ]);
    }

    public function update(UpdateUpsRequest $request, Ups $ups)
    {
        $this->authorize('update', $ups);

        $data = $request->validated();
        $data['activo'] = (bool) ($data['activo'] ?? $ups->activo);

        if ($request->hasFile('foto')) {
            if ($ups->foto && Storage::disk('public')->exists($ups->foto)) {
                Storage::disk('public')->delete($ups->foto);
            }
            $data['foto'] = $request->file('foto')->store('ups/fotos', 'public');
        }

        $ups->update($data);

        return redirect()
            ->route('ups.show', ['ups' => $ups->id])
            ->with('message', 'UPS actualizada exitosamente.');
    }

    public function destroy(Ups $ups)
    {
        $this->authorize('delete', $ups);

        // Borrar archivos asociados antes de eliminar (DB hace cascade pero storage no)
        $ups->load(['mantenimientos.documentos', 'mantenimientos.imagenes']);
        if ($ups->foto && Storage::disk('public')->exists($ups->foto)) {
            Storage::disk('public')->delete($ups->foto);
        }
        foreach ($ups->mantenimientos as $m) {
            foreach ($m->documentos as $doc) {
                if ($doc->ruta_documento && Storage::disk('public')->exists($doc->ruta_documento)) {
                    Storage::disk('public')->delete($doc->ruta_documento);
                }
            }
            foreach ($m->imagenes as $img) {
                if ($img->ruta_imagen && Storage::disk('public')->exists($img->ruta_imagen)) {
                    Storage::disk('public')->delete($img->ruta_imagen);
                }
            }
        }

        $ups->delete();

        return redirect()
            ->route('ups.index')
            ->with('message', 'UPS eliminada.');
    }
}
