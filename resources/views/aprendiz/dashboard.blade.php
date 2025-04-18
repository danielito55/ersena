<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERSENA - Panel del Aprendiz</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="icon" href="{{ asset('img/icon/icon.ico') }}" type="image/png">
</head>
<body>
    <!-- Botón de menú móvil -->
    <button class="mobile-menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="dashboard-container">
        <!-- Sidebar mejorado -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo-container">
                    <img src="{{ asset('img/logo/logo.png') }}" alt="ERSENA Logo">
                    <span class="logo-text">ERSENA</span>
                </div>
            </div>

            <div class="user-profile">
                <div class="profile-image" onclick="document.getElementById('foto_perfil').click()">
                    <img src="{{ $user->profile_photo ? asset('storage/' . $user->profile_photo) : asset('img/default/sena.png') }}" 
                         alt="Foto de perfil" 
                         id="profileImage">
                    <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*" style="display: none;">
                </div>
                <div class="user-info">
                    <h3>{{ explode(' ', $user->nombres_completos)[0] }}</h3>
                    <p>Aprendiz SENA</p>
                </div>
            </div>

            <nav class="sidebar-nav">
                <a href="#dashboard" class="nav-item active" data-section="dashboard">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#registros" class="nav-item" data-section="registros">
                    <i class="fas fa-history"></i>
                    <span>Registros</span>
                </a>
                <a href="#equipo" class="nav-item" data-section="equipo">
                    <i class="fas fa-laptop"></i>
                    <span>Equipo</span>
                </a>
                <a href="#qr" class="nav-item" data-section="qr">
                    <i class="fas fa-qrcode"></i>
                    <span>QR</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-item logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Cerrar Sesión</span>
                    </button>
                </form>
            </nav>
        </aside>

        <!-- Contenido principal optimizado -->
        <main class="main-content">
            <!-- Dashboard Section -->
            <section id="dashboard" class="content-section active">
                <div class="section-header">
                    <h1>Bienvenido, {{ explode(' ', $user->nombres_completos)[0] }}</h1>
                    <p>{{ now()->locale('es')->isoFormat('dddd, D [de] MMMM') }}</p>
                </div>

                <div class="dashboard-grid">
                    <!-- Estado Actual -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <h3>Estado Actual</h3>
                        </div>
                        <div class="status-badge {{ $estadoActual === 'dentro' ? 'success' : 'danger' }}">
                            <i class="fas fa-{{ $estadoActual === 'dentro' ? 'check-circle' : 'times-circle' }}"></i>
                            {{ $estadoActual === 'dentro' ? 'En el SENA' : 'Fuera del SENA' }}
                        </div>
                    </div>

                    <!-- Último Registro -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h3>Último Registro</h3>
                        </div>
                        @if($ultimoRegistro)
                            <div class="status-badge {{ $ultimoRegistro->tipo === 'entrada' ? 'success' : 'danger' }}">
                                <i class="fas fa-sign-{{ $ultimoRegistro->tipo === 'entrada' ? 'in' : 'out' }}-alt"></i>
                                {{ $ultimoRegistro->tipo === 'entrada' ? 'Entrada' : 'Salida' }} - 
                                {{ $ultimoRegistro->fecha_hora->format('h:i A') }}
                            </div>
                        @else
                            <div class="empty-state">
                                <p>Sin registros</p>
                            </div>
                        @endif
                    </div>

                    <!-- Información del Programa -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <h3>Programa</h3>
                        </div>
                        @if($programa)
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-label">Programa</div>
                                    <div class="info-value">{{ $programa->nombre_programa }}</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Ficha</div>
                                    <div class="info-value">{{ $programa->numero_ficha }}</div>
                                </div>
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="fas fa-folder-open"></i>
                                <p>Sin información</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Registros Recientes -->
                <div class="records-container">
                    <div class="records-header">
                        <h2>Registros Recientes</h2>
                    </div>
                    <div class="records-list">
                        @forelse($registrosRecientes as $registro)
                            <div class="record-item">
                                <div class="record-icon">
                                    <i class="fas fa-sign-{{ $registro->tipo === 'entrada' ? 'in' : 'out' }}-alt"></i>
                                </div>
                                <div class="record-info">
                                    <div class="record-title">{{ $registro->tipo === 'entrada' ? 'Entrada' : 'Salida' }}</div>
                                    <div class="record-subtitle">{{ $registro->fecha_hora->format('d/m/Y h:i A') }}</div>
                                </div>
                                <div class="status-badge {{ $registro->tipo === 'entrada' ? 'success' : 'danger' }}">
                                    {{ $registro->tipo === 'entrada' ? 'Entrada' : 'Salida' }}
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                <i class="fas fa-calendar-times"></i>
                                <p>No hay registros recientes</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>

            <!-- Registros Section -->
            <section id="registros" class="content-section">
                <div class="section-header">
                    <h1>Mis Registros</h1>
                    <div class="date-filters">
                        <input type="date" id="fecha_inicio" class="filter-input" value="{{ now()->subMonth()->format('Y-m-d') }}">
                        <input type="date" id="fecha_fin" class="filter-input" value="{{ now()->format('Y-m-d') }}">
                        <button onclick="filtrarRegistros()" class="btn btn-primary">
                            <i class="fas fa-filter"></i>
                            Filtrar
                        </button>
                    </div>
                </div>

                <div class="records-container">
                    <div class="records-list" id="lista-registros">
                        @foreach($registros as $registro)
                            <div class="record-item">
                                <div class="record-icon">
                                    <i class="fas fa-sign-{{ $registro->tipo === 'entrada' ? 'in' : 'out' }}-alt"></i>
                                </div>
                                <div class="record-info">
                                    <div class="record-title">{{ $registro->tipo === 'entrada' ? 'Entrada' : 'Salida' }}</div>
                                    <div class="record-subtitle">{{ $registro->fecha_hora->format('d/m/Y h:i A') }}</div>
                                </div>
                                <div class="status-badge {{ $registro->tipo === 'entrada' ? 'success' : 'danger' }}">
                                    {{ $registro->tipo === 'entrada' ? 'Entrada' : 'Salida' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <!-- Equipo Section -->
            <section id="equipo" class="content-section">
                <div class="section-header">
                    <h1>Mi Equipo</h1>
                </div>

                <div class="dashboard-grid">
                    @forelse($devices as $device)
                        <div class="dashboard-card">
                            <div class="card-header">
                                <div class="card-icon">
                                    <i class="fas fa-laptop"></i>
                                </div>
                                <h3>{{ $device->marca }}</h3>
                            </div>
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-label">Serial</div>
                                    <div class="info-value">{{ $device->serial }}</div>
                                </div>
                            </div>
                            <div class="device-image">
                                <img src="{{ asset('storage/' . $device->foto_serial) }}" 
                                     alt="Foto Serial" 
                                     loading="lazy">
                            </div>
                        </div>
                    @empty
                        <div class="dashboard-card">
                            <div class="empty-state">
                                <i class="fas fa-laptop-code"></i>
                                <p>No tienes equipos registrados</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </section>

            <!-- QR Section -->
            <section id="qr" class="content-section">
                <div class="section-header">
                    <h1>Mi Código QR</h1>
                </div>

                <div class="dashboard-card">
                    <div class="qr-container">
                        <div class="qr-image">
                            <img src="{{ asset('storage/qr_codes/' . $user->qr_code) }}" 
                                 alt="Mi código QR"
                                 loading="lazy">
                        </div>
                        <button onclick="descargarQR()" class="btn btn-primary">
                            <i class="fas fa-download"></i>
                            Descargar QR
                        </button>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Manejo del menú móvil
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.querySelector('.main-content');

        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
        });

        // Cerrar menú al hacer clic fuera
        mainContent.addEventListener('click', function() {
            if (sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        // Navegación
        const navItems = document.querySelectorAll('.nav-item[data-section]');
        const sections = document.querySelectorAll('.content-section');

        function cambiarSeccion(seccionId) {
            sections.forEach(section => {
                section.classList.remove('active');
                if (section.id === seccionId) {
                    section.classList.add('active');
                }
            });

            navItems.forEach(item => {
                item.classList.remove('active');
                if (item.getAttribute('data-section') === seccionId) {
                    item.classList.add('active');
                }
            });

            // Cerrar menú móvil al cambiar de sección
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        navItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const seccionId = this.getAttribute('data-section');
                cambiarSeccion(seccionId);
                history.pushState(null, '', `#${seccionId}`);
            });
        });

        // Manejo de la navegación por hash
        window.addEventListener('hashchange', function() {
            const hash = window.location.hash.substring(1);
            if (hash) cambiarSeccion(hash);
        });

        // Cargar sección inicial
        const hashInicial = window.location.hash.substring(1);
        if (hashInicial) cambiarSeccion(hashInicial);

        // Actualización de foto de perfil
        const inputFotoPerfil = document.getElementById('foto_perfil');
        if (inputFotoPerfil) {
            inputFotoPerfil.addEventListener('change', async function(e) {
                const file = e.target.files[0];
                if (!file) return;

                const formData = new FormData();
                formData.append('foto_perfil', file);
                formData.append('_token', '{{ csrf_token() }}');

                try {
                    const response = await fetch('{{ route("aprendiz.actualizar-foto") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    if (!response.ok) {
                        const errorData = await response.json();
                        throw new Error(errorData.message || 'Error al procesar la solicitud');
                    }

                    const data = await response.json();
                    if (data.success) {
                        document.querySelectorAll('img[alt="Foto de perfil"]')
                            .forEach(img => {
                                img.src = data.url + '?t=' + new Date().getTime();
                            });
                        alert(data.message);
                    } else {
                        throw new Error(data.message || 'Error al actualizar la foto');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert(error.message || 'Error al procesar la solicitud');
                }
            });
        }
    });

    // Filtrado de registros
    async function filtrarRegistros() {
        const fechaInicio = document.getElementById('fecha_inicio').value;
        const fechaFin = document.getElementById('fecha_fin').value;

        try {
            const response = await fetch('{{ route("aprendiz.filtrar-registros") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ fecha_inicio: fechaInicio, fecha_fin: fechaFin })
            });

            const data = await response.json();
            if (data.success) {
                const listaRegistros = document.getElementById('lista-registros');
                listaRegistros.innerHTML = data.registros.map(registro => `
                    <div class="record-item">
                        <div class="record-icon">
                            <i class="fas fa-sign-${registro.tipo === 'entrada' ? 'in' : 'out'}-alt"></i>
                        </div>
                        <div class="record-info">
                            <div class="record-title">${registro.tipo === 'entrada' ? 'Entrada' : 'Salida'}</div>
                            <div class="record-subtitle">${new Date(registro.fecha_hora).toLocaleDateString('es-ES', {
                                day: '2-digit',
                                month: '2-digit',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            })}</div>
                        </div>
                        <div class="status-badge ${registro.tipo === 'entrada' ? 'success' : 'danger'}">
                            ${registro.tipo === 'entrada' ? 'Entrada' : 'Salida'}
                        </div>
                    </div>
                `).join('');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al filtrar los registros');
        }
    }

    // Descarga de QR
    function descargarQR() {
        const qrImage = document.querySelector('.qr-image img');
        const link = document.createElement('a');
        link.href = qrImage.src;
        link.download = 'mi_codigo_qr.png';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    </script>
</body>
</html>