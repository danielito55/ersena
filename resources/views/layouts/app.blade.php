<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'ERSENA') }}</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    @yield('styles')
</head>
<body>
    <header class="header">
        <div class="logo">
            <a href="{{ url('/') }}">
                <img src="{{ asset('img/logo/logo.png') }}" alt="ERSENA Logo">
            </a>
        </div>
        <nav class="nav-links">
            @guest
                <a href="{{ route('login') }}" class="login-btn">Iniciar Sesión</a>
            @else
                <a href="{{ route('home') }}" class="nav-link">Inicio</a>
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="nav-link" style="border: none; background: none; cursor: pointer;">
                        Cerrar Sesión
                    </button>
                </form>
            @endguest
        </nav>
    </header>

    <main class="main-content">
        <div class="container">
            @yield('content')
        </div>
    </main>

    @yield('scripts')
</body>
</html>