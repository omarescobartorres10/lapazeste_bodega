<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Bodega;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $usuarios = User::with('bodega')->paginate(10);
        $bodegas = Bodega::where('estado', 1)->get();

        return view('pages.usuarios', compact('usuarios', 'bodegas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'usuario' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:4',
            'bodega_id' => 'nullable|exists:bodegas,id',
        ]);

        User::create([
            'nombre' => strtoupper($request->nombre),
            'usuario' => strtolower($request->usuario),
            'password' => Hash::make($request->password),
            'bodega_id' => $request->bodega_id ?: null,
            'estado' => 1,
        ]);

        return back()->with('success', 'Usuario creado exitosamente.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'usuario' => 'required|string|max:255|unique:users,usuario,' . $user->id,
            'password' => 'nullable|string|min:4',
            'bodega_id' => 'nullable|exists:bodegas,id',
        ]);

        $user->update([
            'nombre' => strtoupper($request->nombre),
            'usuario' => strtolower($request->usuario),
            'bodega_id' => $request->bodega_id ?: null,
        ]);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        return back()->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggleEstado($id)
    {
        $user = User::findOrFail($id);
        $user->estado = !$user->estado;
        $user->save();

        return back()->with('success', 'Estado del usuario actualizado.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if (auth()->id() === $user->id) {
            return back()->with('error', 'No puedes eliminar tu propio usuario mientras estás logueado.');
        }

        $user->delete();

        return back()->with('success', 'Usuario eliminado correctamente.');
    }
}
