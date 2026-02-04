<?php

namespace App\Http\Controllers;

use App\Models\Moneda;
use Illuminate\Http\Request;

class MonedaController extends Controller
{
    public function index(Request $request)
    {
        $soloActivos = $request->boolean('solo_activos', true);

        $items = Moneda::query()
            ->when($soloActivos, fn($q) => $q->where('activo', true))
            ->orderBy('orden')->orderBy('id')
            ->get();

        return response()->json(['items' => $items]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo' => 'required|string|max:10|unique:monedas,codigo',
            'nombre' => 'required|string|max:80',
            'simbolo'=> 'nullable|string|max:10',
            'orden'  => 'nullable|integer',
            'activo' => 'nullable|boolean',
        ]);

        $data['orden']  = $data['orden'] ?? 0;
        $data['activo'] = array_key_exists('activo', $data) ? (bool)$data['activo'] : true;

        return Moneda::create($data);
    }

    public function update(Request $request, Moneda $moneda)
    {
        $data = $request->validate([
            'codigo' => 'sometimes|required|string|max:10|unique:monedas,codigo,' . $moneda->id,
            'nombre' => 'sometimes|required|string|max:80',
            'simbolo'=> 'nullable|string|max:10',
            'orden'  => 'nullable|integer',
            'activo' => 'nullable|boolean',
        ]);

        $moneda->update($data);
        return $moneda;
    }

    public function destroy(Moneda $moneda)
    {
        $moneda->delete();
        return response()->json(['message' => 'OK']);
    }
}
