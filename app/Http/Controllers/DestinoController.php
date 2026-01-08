<?php

namespace App\Http\Controllers;

use App\Models\Destino;
use Illuminate\Http\Request;

class DestinoController extends Controller
{
    /**
     * Display a listing of destinations.
     */
    public function index()
    {
        $destinos = Destino::orderBy('orden')->get();
        return view('destinos.index', compact('destinos'));
    }

    /**
     * Show the form for creating a new destination.
     */
    public function create()
    {
        return view('destinos.create');
    }

    /**
     * Store a newly created destination.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'orden' => 'nullable|integer',
            'activo' => 'sometimes'
        ]);

        $validated['activo'] = $request->has('activo');
        $validated['orden'] = $validated['orden'] ?? 0;

        Destino::create($validated);

        return redirect()->route('destinos.index')
            ->with('success', 'Destino creado exitosamente');
    }

    /**
     * Show the form for editing the specified destination.
     */
    public function edit(Destino $destino)
    {
        return view('destinos.edit', compact('destino'));
    }

    /**
     * Update the specified destination.
     */
    public function update(Request $request, Destino $destino)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'orden' => 'nullable|integer',
            'activo' => 'sometimes'
        ]);

        $validated['activo'] = $request->has('activo');

        $destino->update($validated);

        return redirect()->route('destinos.index')
            ->with('success', 'Destino actualizado exitosamente');
    }

    /**
     * Remove the specified destination.
     */
    public function destroy(Destino $destino)
    {
        // Verificar si hay pacientes con este destino
        if ($destino->pacientes()->count() > 0) {
            return redirect()->route('destinos.index')
                ->with('error', 'No se puede eliminar el destino porque tiene pacientes asignados');
        }

        $destino->delete();

        return redirect()->route('destinos.index')
            ->with('success', 'Destino eliminado exitosamente');
    }
}
