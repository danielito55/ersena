<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\User;
use Carbon\Carbon;

class RankingPuntualidad extends Component
{
    public $periodo = 'mes';
    public $usuarios;
    public $limite = 10;

    public function mount()
    {
        $this->actualizarRanking();
    }

    public function actualizarRanking()
    {
        // Aquí implementaremos la lógica del ranking cuando tengamos el modelo de asistencia
        // Por ahora, mostraremos datos de ejemplo
        $this->usuarios = collect([
            [
                'posicion' => 1,
                'nombre' => 'Juan Pérez',
                'puntualidad' => '98%',
                'asistencias' => 22,
            ],
            [
                'posicion' => 2,
                'nombre' => 'María García',
                'puntualidad' => '96%',
                'asistencias' => 21,
            ],
            [
                'posicion' => 3,
                'nombre' => 'Carlos López',
                'puntualidad' => '95%',
                'asistencias' => 21,
            ],
        ]);
    }

    public function cambiarPeriodo($nuevoPeriodo)
    {
        $this->periodo = $nuevoPeriodo;
        $this->actualizarRanking();
    }

    public function render()
    {
        return view('livewire.ranking-puntualidad');
    }
}