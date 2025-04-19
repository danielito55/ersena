<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escaneo SENA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.8"></script>
    <link rel="icon" href="{{ asset('img/icon/icon.ico') }}" alt="Icono SENA">
    <style>
        :root {
            --primary-color: #39A900;
            --accent-color: #38ef7d;
            --text-color: #2C3E50;
            --light-bg: #f0f4f8;
            --white: #ffffff;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--light-bg);
            min-height: 100vh;
            color: var(--text-color);
        }
        
        .header {
            background: var(--white);
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
        }
        
        .logo img {
            height: 40px;
            width: auto;
        }
        
        .container {
            padding: 16px;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .card {
            background: var(--white);
            border-radius: 16px;
            box-shadow: var(--shadow);
            padding: 20px;
            margin-bottom: 16px;
            transition: transform 0.3s ease;
        }
        
        .card h2 {
            font-size: 1.2rem;
            margin-bottom: 16px;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        #reader {
            width: 100%;
            border-radius: 12px;
            overflow: hidden;
            aspect-ratio: 1/1;
            max-height: 60vh;
        }
        
        .search-box {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }
        
        .form-control {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            background: #f8fafc;
            transition: border-color 0.2s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(57, 169, 0, 0.1);
        }
        
        .btn {
            padding: 12px 16px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(57, 169, 0, 0.2);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn-entrada {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            width: 100%;
            padding: 16px;
            font-size: 1.1rem;
        }
        
        .btn-salida {
            background: linear-gradient(135deg, #4b5563, #374151);
            width: 100%;
            padding: 16px;
            font-size: 1.1rem;
        }
        
        #notification {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--primary-color);
            color: white;
            padding: 16px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
            display: none;
            z-index: 1000;
            animation: slideUp 0.3s ease-out forwards;
        }
        
        @keyframes slideUp {
            from { transform: translate(-50%, 100%); opacity: 0; }
            to { transform: translate(-50%, 0); opacity: 1; }
        }
        
        #aprendiz-info {
            display: none;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-label {
            font-weight: bold;
            color: #64748b;
        }
        
        .info-value {
            color: #334155;
        }
        
        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 20px;
        }
        
        /* Loading spinner */
        .loader {
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            animation: spin 1s linear infinite;
            margin-right: 10px;
            display: none;
        }
        
        .btn.loading .loader {
            display: inline-block;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <img src="{{ asset('img/logo/logo.png') }}" alt="Logo SENA">
        </div>
        <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
            @csrf
            <button type="submit" class="btn">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </form>
    </div>

    <div class="container">
        <!-- Scanner QR -->
        <div class="card">
            <h2><i class="fas fa-qrcode"></i> Escanear código QR</h2>
            <div id="reader"></div>
        </div>

        <!-- Búsqueda manual -->
        <div class="card">
            <h2><i class="fas fa-search"></i> Buscar por documento</h2>
            <div class="search-box">
                <input type="text" id="documento" class="form-control" placeholder="Número de documento">
                <button onclick="buscarAprendiz()" class="btn" id="btn-buscar">
                    <span class="loader"></span>
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>

        <!-- Información del aprendiz -->
        <div id="aprendiz-info" class="card">
            <h2><i class="fas fa-user"></i> Información del Aprendiz</h2>
            <div class="info-item">
                <span class="info-label">Nombre:</span>
                <span id="nombre-aprendiz" class="info-value"></span>
            </div>
            <div class="info-item">
                <span class="info-label">Documento:</span>
                <span id="documento-aprendiz" class="info-value"></span>
            </div>
            <div class="info-item">
                <span class="info-label">Programa:</span>
                <span id="programa-aprendiz" class="info-value"></span>
            </div>
            <div class="info-item">
                <span class="info-label">Ficha:</span>
                <span id="ficha-aprendiz" class="info-value"></span>
            </div>
            
            <div class="btn-group">
                <button id="btn-entrada" onclick="registrarAsistencia('entrada')" class="btn btn-entrada">
                    <span class="loader"></span>
                    <i class="fas fa-sign-in-alt"></i> Registrar Entrada
                </button>
                <button id="btn-salida" onclick="registrarAsistencia('salida')" class="btn btn-salida">
                    <span class="loader"></span>
                    <i class="fas fa-sign-out-alt"></i> Registrar Salida
                </button>
            </div>
        </div>
    </div>

    <!-- Notification toast -->
    <div id="notification"></div>

    <script>
        // Configuración del escáner QR
        const html5QrCode = new Html5Qrcode("reader");
        const qrConfig = {
            fps: 10,
            qrbox: {
                width: 250,
                height: 250
            }
        };

        function iniciarEscanerQR() {
            Html5Qrcode.getCameras().then(devices => {
                if (devices && devices.length) {
                    // Intenta usar la cámara trasera primero
                    const camaraTrasera = devices.find(device => /(back|rear)/i.test(device.label));
                    const camaraId = camaraTrasera ? camaraTrasera.id : devices[0].id;

                    html5QrCode.start(
                        camaraId, 
                        qrConfig,
                        onScanSuccess,
                        (errorMessage) => {
                            // Maneja los errores silenciosamente durante el escaneo
                        }
                    ).catch((err) => {
                        console.error(`Error al iniciar el escáner: ${err}`);
                        mostrarNotificacion('No se pudo acceder a la cámara. Verifique los permisos.', 'error');
                    });
                } else {
                    mostrarNotificacion('No se detectaron cámaras en el dispositivo', 'error');
                }
            }).catch(err => {
                console.error(`Error al enumerar cámaras: ${err}`);
                mostrarNotificacion('Error al acceder a las cámaras', 'error');
            });
        }

        // Función para detener el escáner
        function detenerEscanerQR() {
            html5QrCode.stop().catch(err => {
                console.error(`Error al detener el escáner: ${err}`);
            });
        }

        // Iniciar el escáner cuando la página esté lista
        document.addEventListener('DOMContentLoaded', () => {
            iniciarEscanerQR();
        });

        // Detener el escáner cuando la página se cierre o se oculte
        window.addEventListener('beforeunload', () => {
            detenerEscanerQR();
        });

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                detenerEscanerQR();
            } else {
                iniciarEscanerQR();
            }
        });

        function onScanSuccess(decodedText, decodedResult) {
            // Reproducir un sonido de éxito

            const beep = new Audio('{{ asset('sounds/entrada.mp3') }}');
            beep.play().catch(e => console.log('No se pudo reproducir el sonido'));
            
            // Vibrar el dispositivo si está disponible
            if (navigator.vibrate) {
                navigator.vibrate(200);
            }
            
            buscarAprendizPorQR(decodedText);
        }

        // Función para buscar aprendiz por documento
        function buscarAprendiz() {
            let documento = document.getElementById('documento').value;
            if (!documento) {
                mostrarNotificacion('Ingrese un número de documento', 'error');
                return;
            }
            
            mostrarCargando('btn-buscar', true);
            verificarAsistencia(documento);
        }

        // Función para buscar aprendiz por QR
        function buscarAprendizPorQR(qrCode) {
            $.ajax({
                url: '/admin/buscar-por-qr',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    qr_code: qrCode
                },
                success: function(response) {
                    mostrarInformacionAprendiz(response);
                },
                error: function(error) {
                    mostrarNotificacion('Error al buscar aprendiz', 'error');
                }
            });
        }

        // Verificar asistencia y mostrar botones correspondientes
        function verificarAsistencia(documento) {
            $.ajax({
                url: '/admin/verificar-asistencia',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    documento_identidad: documento
                },
                success: function(response) {
                    mostrarCargando('btn-buscar', false);
                    mostrarInformacionAprendiz(response);
                },
                error: function(error) {
                    mostrarCargando('btn-buscar', false);
                    mostrarNotificacion('Error al verificar asistencia', 'error');
                }
            });
        }

        // Mostrar información del aprendiz y los botones correspondientes
        function mostrarInformacionAprendiz(data) {
            document.getElementById('aprendiz-info').style.display = 'block';
            document.getElementById('nombre-aprendiz').textContent = data.user.nombres_completos;
            document.getElementById('documento-aprendiz').textContent = data.user.documento_identidad;
            
            // Información del programa
            if (data.user.programa_formacion) {
                document.getElementById('programa-aprendiz').textContent = data.user.programa_formacion.nombre_programa;
                document.getElementById('ficha-aprendiz').textContent = data.user.programa_formacion.numero_ficha;
            } else {
                document.getElementById('programa-aprendiz').textContent = 'N/A';
                document.getElementById('ficha-aprendiz').textContent = 'N/A';
            }

            // Mostrar/ocultar botones según el estado de asistencia
            document.getElementById('btn-entrada').style.display = data.puede_registrar_entrada ? 'flex' : 'none';
            document.getElementById('btn-salida').style.display = data.puede_registrar_salida ? 'flex' : 'none';
            
            // Scroll a la información
            document.getElementById('aprendiz-info').scrollIntoView({ behavior: 'smooth' });
        }

        // Registrar asistencia
        function registrarAsistencia(tipo) {
            let documento = document.getElementById('documento-aprendiz').textContent;
            const btnId = tipo === 'entrada' ? 'btn-entrada' : 'btn-salida';
            mostrarCargando(btnId, true);
            
            $.ajax({
                url: '/admin/registrar-asistencia',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    documento_identidad: documento,
                    tipo: tipo
                },
                success: function(response) {
                    mostrarCargando(btnId, false);
                    const mensaje = tipo === 'entrada' ? 'Entrada registrada correctamente' : 'Salida registrada correctamente';
                    mostrarNotificacion(mensaje, 'success');
                    
                    // Ocultar el botón correspondiente
                    document.getElementById(btnId).style.display = 'none';
                },
                error: function(error) {
                    mostrarCargando(btnId, false);
                    mostrarNotificacion('Error al registrar asistencia', 'error');
                }
            });
        }
        
        // Mostrar notificación
        function mostrarNotificacion(mensaje, tipo) {
            const notificacion = document.getElementById('notification');
            notificacion.textContent = mensaje;
            notificacion.style.display = 'block';
            
            // Cambiar color según el tipo
            if (tipo === 'error') {
                notificacion.style.background = '#ef4444';
            } else {
                notificacion.style.background = '#10b981';
            }
            
            // Ocultar después de 3 segundos
            setTimeout(() => {
                notificacion.style.opacity = '0';
                setTimeout(() => {
                    notificacion.style.display = 'none';
                    notificacion.style.opacity = '1';
                }, 300);
            }, 3000);
        }
        
        // Mostrar/ocultar indicador de carga
        function mostrarCargando(btnId, mostrar) {
            const btn = document.getElementById(btnId);
            if (mostrar) {
                btn.classList.add('loading');
                btn.setAttribute('disabled', true);
            } else {
                btn.classList.remove('loading');
                btn.removeAttribute('disabled');
            }
        }
    </script>
</body>
</html>