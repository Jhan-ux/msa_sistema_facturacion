<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Ingrese su usuario.',
            'password.required' => 'Ingrese su contraseña.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password'], 'activo' => true], $remember) ||
            Auth::attempt(['email' => $credentials['username'], 'password' => $credentials['password'], 'activo' => true], $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'))->with('success', '¡Bienvenido al sistema, ' . Auth::user()->name . '!');
        }

        return back()->withErrors([
            'username' => 'Las credenciales proporcionadas son incorrectas o el usuario está inactivo.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Sesión cerrada correctamente.');
    }
}
