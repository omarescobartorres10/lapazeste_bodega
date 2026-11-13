<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Muestra la vista de login.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('panel');
        }

        return view('auth.login');
    }

    /**
     * Procesa el inicio de sesión.
     */
    public function login(Request $request)
    {
        $request->validate([
            'usuario' => 'required|string',
            'password' => 'required|string',
        ]);

        // Buscar usuario por nombre de usuario
        $user = User::where('usuario', $request->usuario)->first();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Usuario o contraseña incorrectos.');
        }

        // Verificar si el usuario está activo
        if (isset($user->estado) && $user->estado != 1) {
            return redirect()->route('login')->with('error', 'Este usuario está inactivo. No puede iniciar sesión.');
        }

        // ✅ Verificar contraseña encriptada correctamente
        if (!Hash::check($request->password, $user->password)) {
            return redirect()->route('login')->with('error', 'Usuario o contraseña incorrectos.');
        }

        // Iniciar sesión
        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('last_activity', time());

        return redirect()->intended('panel')->with('success', '¡Bienvenido al sistema!');
    }

    /**
     * Cierra sesion el usuario
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        session()->flash('success', 'Sesión cerrada correctamente.');
        return redirect('/login');
    }

}
