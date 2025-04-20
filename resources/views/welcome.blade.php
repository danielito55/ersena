<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'ERSENA') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    </head>
    <body>
        <div class="layout">
            <!-- Barra Superior -->
            <div class="top-bar">
                <div class="logo">
                    <a href="{{ url('/') }}">
                        <img src="{{ asset('img/logo/logo.png') }}" alt="ERSENA Logo">
                    </a>
                </div>
                <nav>
                    <a href="{{ route('login') }}" class="login-btn">Iniciar Sesión</a>
                </nav>
            </div>

            <!-- Contenido Principal -->
            <div class="main-container">
                <!-- Panel Izquierdo -->
                <div class="side-panel">
                    <div class="welcome-content">
                        <h2>Panel de Control</h2>
                        <div class="ranking-section">
                            
                        </div>
                    </div>
                </div>

                <!-- Panel Principal -->
                <div class="main-panel">
                    <div class="welcome-content">
                        <h1 class="welcome-text">Bienvenido a ERSENA</h1>
                        <p class="subtitle">"Hacer lo correcto, aunque sea difícil, es la mejor forma de ser humano."</p>
                    </div>
                </div>
            </div>
        </div>
        @livewireScripts
    </body>
</html>
