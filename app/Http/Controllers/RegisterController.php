<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Device;
use App\Models\ProgramaFormacion;
use App\Mail\WelcomeEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register'); // Crear esta vista más adelante
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'nombres_completos' => 'required|string|max:255',
            'documento_identidad' => 'required|string|max:255|unique:users',
            'correo' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'nombre_programa' => 'required|string|max:255',
            'numero_ficha' => 'required|string|max:255',
            'numero_ambiente' => 'required|string|max:255',
            'marca' => 'required|string|max:255',
            'serial' => 'required|string|max:255',
            'foto_serial' => 'required|image',
            'jornada_id' => 'required|exists:jornadas,id',
        ]);

        // Generar un identificador único para el QR que incluya el documento de identidad
        $qrIdentifier = $validated['documento_identidad'] . '-' . Str::random(10);

        $user = User::create([
            'nombres_completos' => $validated['nombres_completos'],
            'documento_identidad' => $validated['documento_identidad'],
            'correo' => $validated['correo'],
            'password' => Hash::make($validated['password']),
            'rol' => 'aprendiz',
            'qr_code' => $qrIdentifier,
            'jornada_id' => $validated['jornada_id'],
        ]);

        // Crear el registro del programa de formación
        ProgramaFormacion::create([
            'user_id' => $user->id,
            'nombre_programa' => $validated['nombre_programa'],
            'numero_ficha' => $validated['numero_ficha'],
            'numero_ambiente' => $validated['numero_ambiente'],
        ]);

        $fotoPath = $request->file('foto_serial')->store('serial_photos', 'public');

        Device::create([
            'user_id' => $user->id,
            'marca' => $validated['marca'],
            'serial' => $validated['serial'],
            'foto_serial' => $fotoPath,
        ]);

        // Enviar correo de bienvenida con el código QR
        try {
            Mail::to($user->correo)->send(new WelcomeEmail($user, $qrIdentifier));
        } catch (\Exception $e) {
            Log::error('Error enviando correo: ' . $e->getMessage());
        }

        return redirect()->route('login')
            ->with('success', 'Registro exitoso. Por favor revisa tu correo para obtener tu código QR.');
    }
}
