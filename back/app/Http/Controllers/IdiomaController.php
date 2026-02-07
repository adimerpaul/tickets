<?php

namespace App\Http\Controllers;

use App\Models\Idioma;
use Illuminate\Http\Request;

class IdiomaController extends Controller
{
    public function index(Request $request)
    {
        $soloActivos = $request->boolean('solo_activos', true);

        $items = Idioma::query()
            ->when($soloActivos, fn($q) => $q->where('activo', true))
            ->orderBy('orden')->orderBy('id')
            ->get();

        return response()->json(['items' => $items]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo' => 'required|string|max:10|unique:idiomas,codigo',
            'nombre' => 'required|string|max:80',
            'orden'  => 'nullable|integer',
            'activo' => 'nullable|boolean',
        ]);

        $data['orden']  = $data['orden'] ?? 0;
        $data['activo'] = array_key_exists('activo', $data) ? (bool)$data['activo'] : true;

        return Idioma::create($data);
    }

    public function update(Request $request, Idioma $idioma)
    {
        $data = $request->validate([
            'codigo' => 'sometimes|required|string|max:10|unique:idiomas,codigo,' . $idioma->id,
            'nombre' => 'sometimes|required|string|max:80',
            'orden'  => 'nullable|integer',
            'activo' => 'nullable|boolean',
        ]);

        $idioma->update($data);
        return $idioma;
    }

    public function destroy(Idioma $idioma)
    {
        $idioma->delete();
        return response()->json(['message' => 'OK']);
    }
}
