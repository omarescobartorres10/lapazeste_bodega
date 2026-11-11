<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Codigo;

class CodigoController extends Controller
{
    public function validar(Request $request)
    {
        $codigo = $request->input('codigo');

        $existe = Codigo::where('codigo', $codigo)->exists();

        return response()->json(['valido' => $existe]);
    }
}
