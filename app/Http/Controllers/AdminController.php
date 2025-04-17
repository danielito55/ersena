<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Asistencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AdminController extends Controller
{
    public function dashboard()
    {
        $page = request()->get('page', 1);
        $perPage = 20;

        $asistencias = Asistencia::with(['user.programaFormacion', 'user.devices'])
            ->whereDate('fecha_hora', '>=', now()->setTimezone('America/Bogota')->subDays(30))
            ->orderBy('fecha_hora', 'desc')
            ->get();

        $grupos = $asistencias->groupBy(function($asistencia) {
            return $asistencia->user_id . '_' . $asistencia->fecha_hora->setTimezone('America/Bogota')->format('Y-m-d');
        })->map(function($grupo) {
            return $grupo->map(function($asistencia) {
                return $asistencia->toArray();
            });
        });

        $items = new Collection($grupos);
        $items = $items->values();

        $paginatedItems = new LengthAwarePaginator(
            $items->forPage($page, $perPage),
            $items->count(),
            $perPage,
            $page,
            ['path' => request()->url()]
        );

        return view('admin.dashboard', ['asistencias' => $paginatedItems]);
    }

    public function verificarAsistencia(Request $request)
    {
        $request->validate([
            'documento_identidad' => 'required|string',
        ]);

        $user = User::where('documento_identidad', $request->documento_identidad)
            ->where('rol', 'aprendiz')
            ->with(['programaFormacion', 'devices'])
            ->first();

        if (!$user) {
            return response()->json(['error' => 'Aprendiz no encontrado'], 404);
        }

        $asistenciaHoy = Asistencia::where('user_id', $user->id)
            ->whereDate('fecha_hora', now()->setTimezone('America/Bogota')->format('Y-m-d'))
            ->orderBy('fecha_hora', 'desc')
            ->first();

        $puedeRegistrarEntrada = !$asistenciaHoy || 
            ($asistenciaHoy && $asistenciaHoy->tipo === 'salida');
        
        $puedeRegistrarSalida = $asistenciaHoy && 
            $asistenciaHoy->tipo === 'entrada';

        return response()->json([
            'user' => $user,
            'puede_registrar_entrada' => $puedeRegistrarEntrada,
            'puede_registrar_salida' => $puedeRegistrarSalida
        ]);
    }

    public function buscarPorQR(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        $user = User::where('qr_code', $request->qr_code)
            ->where('rol', 'aprendiz')
            ->with(['programaFormacion', 'devices'])
            ->first();

        if (!$user) {
            return response()->json(['error' => 'Aprendiz no encontrado'], 404);
        }

        return $this->verificarAsistencia(new Request(['documento_identidad' => $user->documento_identidad]));
    }

    public function registrarAsistencia(Request $request)
    {
        $request->validate([
            'documento_identidad' => 'required|string',
            'tipo' => 'required|in:entrada,salida',
        ]);

        $user = User::where('documento_identidad', $request->documento_identidad)
            ->where('rol', 'aprendiz')
            ->first();

        if (!$user) {
            return response()->json(['error' => 'Aprendiz no encontrado'], 404);
        }

        $existeRegistro = Asistencia::where('user_id', $user->id)
            ->where('tipo', $request->tipo)
            ->whereDate('fecha_hora', now()->setTimezone('America/Bogota')->format('Y-m-d'))
            ->exists();

        if ($existeRegistro) {
            return response()->json([
                'error' => 'Ya existe un registro de ' . $request->tipo . ' para este aprendiz hoy'
            ], 400);
        }

        $asistencia = Asistencia::create([
            'user_id' => $user->id,
            'tipo' => $request->tipo,
            'fecha_hora' => now()->setTimezone('America/Bogota'),
            'registrado_por' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Asistencia registrada correctamente',
            'asistencia' => $asistencia
        ]);
    }
}
