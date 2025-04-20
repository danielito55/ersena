<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <title>{{ config('app.name', 'ERSENA') }}</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('styles')
    
    <script>
        // Prevenir navegación con botones del navegador
        window.onpageshow = function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        };

        // Verificar sesión en cada cambio de ruta
        document.addEventListener('DOMContentLoaded', function() {
            checkSession();
        });

        function checkSession() {
            fetch('/check-session', {
                method: 'GET',
                headers: {
                    'Cache-Control': 'no-cache',
                    'Pragma': 'no-cache',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (!data.authenticated) {
                    window.location.href = '{{ route("login") }}';
                }
            })
            .catch(error => {
                console.error('Error verificando sesión:', error);
                window.location.href = '{{ route("login") }}';
            });
        }

        // Verificar sesión cada 1 minuto
        @auth
            setInterval(checkSession, 60000);
        @endauth

        // Prevenir el uso del botón atrás después del logout
        window.onload = function() {
            if (window.history && window.history.pushState) {
                window.history.pushState('forward', null, window.location.href);
                window.onpopstate = function() {
                    window.history.pushState('forward', null, window.location.href);
                    checkSession();
                };
            }
        }

        // Detectar cuando la pestaña/ventana se vuelve activa
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible') {
                checkSession();
            }
        });
    </script>
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
                <a href="{{ route('register') }}" class="register-btn">Registrarse</a>
            @else
                <div class="nav-menu">
                    <a href="{{ route('home') }}" class="nav-link">Inicio</a>
                    @if(auth()->user()->hasRole('admin'))
                        <a href="{{ route('admin.dashboard') }}" class="nav-link">Panel Admin</a>
                    @endif
                    @if(auth()->user()->hasRole('aprendiz'))
                        <a href="{{ route('aprendiz.dashboard') }}" class="nav-link">Mi Panel</a>
                    @endif
                    <div class="user-menu">
                        <span>{{ Auth::user()->name }}</span>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="nav-link logout-btn" onclick="return confirm('¿Estás seguro que deseas cerrar sesión?')">
                                Cerrar Sesión
                            </button>
                        </form>
                    </div>
                </div>
            @endguest
        </nav>
    </header>

    <main class="main-content">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            @yield('content')
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; {{ date('Y') }} ERSENA. Todos los derechos reservados.</p>
        </div>
    </footer>

    @yield('scripts')
    
    <script>
        // Cerrar sesión automáticamente después de 30 minutos de inactividad
        let inactivityTime = function () {
            let time;
            window.onload = resetTimer;
            document.onmousemove = resetTimer;
            document.onkeydown = resetTimer;

            function logout() {
                document.getElementById('logout-form').submit();
            }

            function resetTimer() {
                clearTimeout(time);
                time = setTimeout(logout, 1800000); // 30 minutos
            }
        };
        @auth
            inactivityTime();
        @endauth
    </script>
</body>
</html>