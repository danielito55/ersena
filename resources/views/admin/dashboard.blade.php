<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - SENA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Outfit:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.8"></script>
    <link rel="icon" href="{{ asset('img/icon/icon.ico') }}" alt="Icono SENA">
</head>
<body>
    <header>
        <div class="logo">
            <img src="{{ asset('img/logo/logo.png') }}" alt="Logo SENA" width="50">
        </div>
        <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
            @csrf
            <button type="submit" class="btn">Cerrar Sesión</button>
        </form>
    </header>

    <div class="container">
        <div class="dashboard-grid">
            <!-- Scanner QR -->
            <div class="card">
                <h2><i class="fas fa-qrcode"></i> Escáner QR</h2>
                <div id="reader"></div>
            </div>

            <!-- Búsqueda manual -->
            <div class="card">
                <h2><i class="fas fa-search"></i> Búsqueda Manual</h2>
                <div class="search-box">
                    <input type="text" id="documento" class="form-control" placeholder="Ingrese número de documento">
                    <button onclick="buscarAprendiz()" class="btn">Buscar</button>
                </div>

                <!-- Información del aprendiz -->
                <div id="aprendiz-info" style="display: none;">
                    <h3>Información del Aprendiz</h3>
                    <div class="info-grid">
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
                        <div class="info-item">
                            <span class="info-label">Ambiente:</span>
                            <span id="ambiente-aprendiz" class="info-value"></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Equipo:</span>
                            <span id="equipo-aprendiz" class="info-value"></span>
                        </div>
                    </div>
                    
                    <div class="btn-group">
                        <button id="btn-entrada" onclick="registrarAsistencia('entrada')" class="btn btn-entrada">
                            <i class="fas fa-sign-in-alt"></i> Registrar Entrada
                        </button>
                        <button id="btn-salida" onclick="registrarAsistencia('salida')" class="btn btn-salida">
                            <i class="fas fa-sign-out-alt"></i> Registrar Salida
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de registros -->
        <div class="table-container">
            <h2><i class="fas fa-clipboard-list"></i> Registro de Asistencias</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Aprendiz</th>
                            <th>Documento</th>
                            <th>Programa</th>
                            <th>Ficha</th>
                            <th>Equipo</th>
                            <th>Hora Entrada</th>
                            <th>Hora Salida</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($asistencias as $asistenciasGrupo)
                            @php
                                $asistenciasArray = is_array($asistenciasGrupo) ? $asistenciasGrupo : $asistenciasGrupo->toArray();
                                $entrada = collect($asistenciasArray)->where('tipo', 'entrada')->first();
                                $salida = collect($asistenciasArray)->where('tipo', 'salida')->first();
                                if (!$entrada) continue;
                            @endphp
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($entrada['fecha_hora'])->format('d/m/Y') }}</td>
                                <td>{{ $entrada['user']['nombres_completos'] }}</td>
                                <td>{{ $entrada['user']['documento_identidad'] }}</td>
                                <td>{{ $entrada['user']['programa_formacion']['nombre_programa'] ?? 'N/A' }}</td>
                                <td>{{ $entrada['user']['programa_formacion']['numero_ficha'] ?? 'N/A' }}</td>
                                <td>{{ isset($entrada['user']['devices'][0]) ? 
                                    ($entrada['user']['devices'][0]['marca'] . ' - ' . 
                                     $entrada['user']['devices'][0]['serial']) : 'N/A' }}</td>
                                <td>{{ \Carbon\Carbon::parse($entrada['fecha_hora'])->format('H:i:s') }}</td>
                                <td>{{ $salida ? \Carbon\Carbon::parse($salida['fecha_hora'])->format('H:i:s') : '-' }}</td>
                                <td>
                                    @if(!$salida)
                                        <button data-documento="{{ $entrada['user']['documento_identidad'] }}" class="btn btn-salida btn-sm btn-registrar-salida">
                                            <i class="fas fa-sign-out-alt"></i> Registrar Salida
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-container">
                {{ $asistencias->links() }}
            </div>
        </div>
    </div>

    <script>
        // Configuración del escáner QR
        const html5QrCode = new Html5Qrcode("reader");
        const qrConfig = {
            fps: 10,
            qrbox: {
                width: 250,
                height: 250
            },
            aspectRatio: 1.0
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
                        alert('No se pudo acceder a la cámara. Por favor, asegúrese de que ha dado permiso para usar la cámara y que está usando HTTPS o localhost.');
                    });
                } else {
                    alert('No se detectaron cámaras en el dispositivo');
                }
            }).catch(err => {
                console.error(`Error al enumerar cámaras: ${err}`);
                alert('Error al acceder a las cámaras del dispositivo');
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
            const beep = new Audio('data:audio/wav;base64,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');
            beep.play().catch(e => console.log('No se pudo reproducir el sonido'));
            
            buscarAprendizPorQR(decodedText);
        }

        // Función para buscar aprendiz por documento
        function buscarAprendiz() {
            let documento = document.getElementById('documento').value;
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
                    alert('Error al buscar aprendiz');
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
                    mostrarInformacionAprendiz(response);
                },
                error: function(error) {
                    alert('Error al verificar asistencia');
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
                document.getElementById('ambiente-aprendiz').textContent = data.user.programa_formacion.numero_ambiente;
            }

            // Información del equipo
            if (data.user.devices && data.user.devices.length > 0) {
                const device = data.user.devices[0];
                document.getElementById('equipo-aprendiz').textContent = `${device.marca} - ${device.serial}`;
            }

            // Mostrar/ocultar botones según el estado de asistencia
            document.getElementById('btn-entrada').style.display = data.puede_registrar_entrada ? 'inline-block' : 'none';
            document.getElementById('btn-salida').style.display = data.puede_registrar_salida ? 'inline-block' : 'none';
        }

        // Registrar asistencia
        function registrarAsistencia(tipo) {
            let documento = document.getElementById('documento-aprendiz').textContent;
            $.ajax({
                url: '/admin/registrar-asistencia',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    documento_identidad: documento,
                    tipo: tipo
                },
                success: function(response) {
                    alert('Asistencia registrada correctamente');
                    location.reload(); // Recargar para actualizar la lista
                },
                error: function(error) {
                    alert('Error al registrar asistencia');
                }
            });
        }

        // Registrar salida directamente desde la tabla
        function registrarSalidaDirecta(documento) {
            $.ajax({
                url: '/admin/registrar-asistencia',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    documento_identidad: documento,
                    tipo: 'salida'
                },
                success: function(response) {
                    alert('Salida registrada correctamente');
                    location.reload();
                },
                error: function(error) {
                    alert('Error al registrar salida');
                }
            });
        }

        // Registrar eventos para los botones de salida directa
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.btn-registrar-salida').forEach(button => {
                button.addEventListener('click', function() {
                    const documento = this.dataset.documento;
                    registrarSalidaDirecta(documento);
                });
            });
        });
    </script>
</body>
</html>