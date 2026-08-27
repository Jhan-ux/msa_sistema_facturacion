<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Muestra la vista del perfil del usuario autenticado.
     */
    public function show()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para ver su perfil.');
        }

        // Cargar relaciones de empresas y sedes asignadas
        $user->load(['empresas', 'sedes']);

        return view('perfil.index', compact('user'));
    }

    /**
     * Actualiza la información básica del perfil (nombre, username, email).
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', 'unique:users,username,' . $user->id],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ], [
            'name.required' => 'El nombre completo es obligatorio.',
            'username.required' => 'El nombre de usuario es obligatorio.',
            'username.unique' => 'Este nombre de usuario ya se encuentra registrado por otro usuario.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingrese una dirección de correo electrónico válida.',
            'email.unique' => 'Este correo electrónico ya está registrado por otro usuario.',
        ]);

        $user->update([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
        ]);

        return redirect()->route('perfil.show')->with('success', '¡Tus datos de perfil han sido actualizados exitosamente!');
    }

    /**
     * Actualiza la contraseña del usuario.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Debe ingresar su contraseña actual.',
            'password.required' => 'Debe ingresar una nueva contraseña.',
            'password.min' => 'La nueva contraseña debe contener al menos 6 caracteres.',
            'password.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
        ]);

        // Verificar coincidencia de contraseña actual
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'La contraseña actual ingresada es incorrecta.',
            ])->with('error_password', true);
        }

        // Actualizar contraseña (el modelo User ya tiene el cast 'password' => 'hashed')
        $user->update([
            'password' => $request->password,
        ]);

        return redirect()->route('perfil.show')->with('success', '¡Tu contraseña ha sido actualizada con éxito!');
    }
}
