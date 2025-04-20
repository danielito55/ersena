<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('throttle:6,1')->only('login');
    }

    public function showLoginForm()
    {
        return view('auth.login'); // Crear esta vista más adelante
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'correo' => 'required|email|max:255',
                'password' => 'required|string',
            ]);

            // Verificar intentos de inicio de sesión
            if ($this->hasTooManyLoginAttempts($request)) {
                $this->fireLockoutEvent($request);
                throw ValidationException::withMessages([
                    'correo' => ['Demasiados intentos de inicio de sesión. Por favor, espere antes de intentar de nuevo.'],
                ]);
            }

            if (Auth::attempt($credentials, $request->filled('remember'))) {
                $request->session()->regenerate();
                $this->clearLoginAttempts($request);

                // Registrar inicio de sesión exitoso
                Log::info('Inicio de sesión exitoso', [
                    'user' => Auth::user()->correo,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);

                // Redirigir según el rol del usuario
                $user = Auth::user();
                if ($user->rol === 'admin') {
                    return redirect()->intended(route('admin.dashboard'));
                } elseif ($user->rol === 'aprendiz') {
                    return redirect()->intended(route('aprendiz.dashboard'));
                }
            }

            // Registrar intento fallido
            $this->incrementLoginAttempts($request);
            Log::warning('Intento de inicio de sesión fallido', [
                'email' => $credentials['correo'],
                'ip' => $request->ip()
            ]);

            throw ValidationException::withMessages([
                'correo' => ['Las credenciales proporcionadas no coinciden con nuestros registros.'],
            ]);
        } catch (\Exception $e) {
            Log::error('Error en el inicio de sesión', [
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return back()->withErrors([
                'error' => 'Ha ocurrido un error al intentar iniciar sesión. Por favor, inténtelo de nuevo.',
            ]);
        }
    }

    public function logout(Request $request)
    {
        // Registrar cierre de sesión
        if (Auth::check()) {
            Log::info('Cierre de sesión', [
                'user' => Auth::user()->correo,
                'ip' => $request->ip()
            ]);
        }

        // Limpiar la sesión y los tokens
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Limpiar todas las cookies relacionadas con la sesión
        $cookies = $request->cookies->all();
        $response = redirect()->route('login')->with('success', 'Has cerrado sesión correctamente.');
        
        foreach ($cookies as $name => $value) {
            if (str_starts_with($name, 'laravel_') || str_starts_with($name, 'XSRF-')) {
                $response->withoutCookie($name);
            }
        }

        return $response;
    }

    protected function hasTooManyLoginAttempts(Request $request)
    {
        return $request->session()->get('login.attempts', 0) >= 5;
    }

    protected function incrementLoginAttempts(Request $request)
    {
        $attempts = $request->session()->get('login.attempts', 0);
        $request->session()->put('login.attempts', $attempts + 1);
    }

    protected function clearLoginAttempts(Request $request)
    {
        $request->session()->forget('login.attempts');
    }

    protected function fireLockoutEvent(Request $request)
    {
        Log::warning('Usuario bloqueado por demasiados intentos', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
    }
}
