<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="theme-color" content="#39A900">
    <title>Login</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Preload fonts -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600&family=Poppins&display=swap" as="style">
    <link rel="preload" as="image" href="{{ asset('img/logo/logo.png') }}">
    
    <!-- Estilos -->
    <link rel="stylesheet" href="{{ asset('css/login.css') }}" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600&family=Poppins&display=swap">
    
    <!-- icon -->
    <link rel="icon" href="{{ asset('img/icon/icon.ico') }}" type="image/x-icon">
    
    <!-- Cache control -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
</head> 
<body class="index-page">

    <header class="index-header">
        <div class="logo">
            <img src="{{ asset('img/logo/logo.png') }}" alt="logo">
        </div>
    </header>

    <main class="index-login-container">
        <div class="index-login-card">
            <h2 class="index-title">Iniciar Sesión</h2>

            {{-- Mensajes de error --}}
            @if ($errors->any())
                <div class="index-alert index-alert-danger">
                    <ul style="margin: 0;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="index-form-group">
                    <label for="correo">Correo:</label>
                    <input type="email" id="correo" name="correo" required>
                </div>

                <div class="index-form-group index-password-container">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="index-btn-login">Iniciar Sesión</button>
            </form>

            <div class="index-registro-link">
                <p>¿No tienes una cuenta? <a href="{{ route('register') }}">Registrate</a></p>
            </div>
        </div>
    </main>

</body>
</html>
