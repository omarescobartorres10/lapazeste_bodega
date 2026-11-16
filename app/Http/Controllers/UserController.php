<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Bodega;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        if (Auth::user()->rol !== 'admin') {
            abort(403, 'No tienes permisos para acceder a la gestión de usuarios.');
        }

        $usuarios = User::with('bodega')->orderBy('id', 'asc')->paginate(10);
        $bodegas = Bodega::where('estado', 1)->orderBy('nombre', 'asc')->get();

        return view('pages.usuarios', compact('usuarios', 'bodegas'));
    }

    public function store(Request $request)
    {
        if (Auth::user()->rol !== 'admin') {
            abort(403, 'No tienes permisos para realizar esta acción.');
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'usuario' => 'required|string|max:255|unique:users,usuario',
            'password' => 'required|string|min:4',
            'rol' => 'required|in:admin,encargado',
            'bodega_id' => 'nullable|required_if:rol,encargado|exists:bodegas,id',
        ], [
            'nombre.required' => 'El nombre completo es obligatorio.',
            'usuario.required' => 'El nombre de usuario es obligatorio.',
            'usuario.unique' => 'Este nombre de usuario ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 4 caracteres.',
            'rol.required' => 'Debe seleccionar un rol.',
            'bodega_id.required_if' => 'Debe asignar una bodega para el usuario encargado.',
            'bodega_id.exists' => 'La bodega seleccionada no es válida.',
        ]);

        $bodegaId = ($request->rol === 'encargado') ? $request->bodega_id : null;

        User::create([
            'nombre' => strtoupper(trim($request->nombre)),
            'usuario' => strtolower(trim($request->usuario)),
            'password' => Hash::make($request->password),
            'rol' => $request->rol,
            'bodega_id' => $bodegaId,
            'estado' => 1,
        ]);

        return back()->with('success', 'Usuario creado exitosamente.');
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->rol !== 'admin') {
            abort(403, 'No tienes permisos para realizar esta acción.');
        }

        $user = User::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'usuario' => 'required|string|max:255|unique:users,usuario,' . $user->id,
            'password' => 'nullable|string|min:4',
            'rol' => 'required|in:admin,encargado',
            'bodega_id' => 'nullable|required_if:rol,encargado|exists:bodegas,id',
        ], [
            'nombre.required' => 'El nombre completo es obligatorio.',
            'usuario.required' => 'El nombre de usuario es obligatorio.',
            'usuario.unique' => 'Este nombre de usuario ya está registrado por otra cuenta.',
            'password.min' => 'La contraseña debe tener al menos 4 caracteres.',
            'rol.required' => 'Debe seleccionar un rol.',
            'bodega_id.required_if' => 'Debe asignar una bodega para el usuario encargado.',
            'bodega_id.exists' => 'La bodega seleccionada no es válida.',
        ]);

        $bodegaId = ($request->rol === 'encargado') ? $request->bodega_id : null;

        $user->update([
            'nombre' => strtoupper(trim($request->nombre)),
            'usuario' => strtolower(trim($request->usuario)),
            'rol' => $request->rol,
            'bodega_id' => $bodegaId,
        ]);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        return back()->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggleEstado($id)
    {
        if (Auth::user()->rol !== 'admin') {
            abort(403, 'No tienes permisos para realizar esta acción.');
        }

        $user = User::findOrFail($id);

        if (Auth::id() === $user->id) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta mientras estás logueado.');
        }

        $user->estado = !$user->estado;
        $user->save();

        $estadoTxt = $user->estado ? 'activado' : 'desactivado';
        return back()->with('success', "Usuario {$estadoTxt} correctamente.");
    }

    public function destroy($id)
    {
        if (Auth::user()->rol !== 'admin') {
            abort(403, 'No tienes permisos para realizar esta acción.');
        }

        $user = User::findOrFail($id);

        if (Auth::id() === $user->id) {
            return back()->with('error', 'No puedes eliminar tu propio usuario mientras estás logueado.');
        }

        $user->delete();

        return back()->with('success', 'Usuario eliminado correctamente.');
    }
}

