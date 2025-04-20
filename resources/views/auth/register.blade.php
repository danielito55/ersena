<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Aprendiz - SENA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Outfit:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="{{ asset('css/register.css') }}" rel="stylesheet">
    <!-- icono -->
    <link rel="icon" href="{{ asset('img/icon/icon.ico') }}" alt="logo">
</head>
<body class="register-page">
    <header class="register-header">
        <div class="register-logo">
            <img src="{{ asset('img/logo/logo.png') }}" alt="Logo SENA">
        </div>
    </header>

    <div class="register-container">
        <div class="register-card">
            <div class="register-header-content">
                <h2 class="register-title">Registro de Aprendiz</h2>
                
                <!-- Indicador de progreso -->
                <div class="progress-container">
                    <div class="progress-bar" id="progress-bar"></div>
                </div>

                <!-- Pasos -->
                <div class="steps-container">
                    <div class="step-item active" data-step="1">
                        <div class="step-number">1</div>
                        <div class="step-text">Datos</div>
                    </div>
                    <div class="step-item" data-step="2">
                        <div class="step-number">2</div>
                        <div class="step-text">Programa</div>
                    </div>
                    <div class="step-item" data-step="3">
                        <div class="step-number">3</div>
                        <div class="step-text">Dispositivo</div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" class="register-form" id="registerForm">
                @csrf
                <div class="sections-container">
                    <!-- Sección 1: Datos Personales -->
                    <div class="form-section active" data-section="1">
                        <div class="form-grid-compact">
                            <div class="register-form-group">
                                <label for="nombres_completos">Nombres Completos</label>
                                <input type="text" id="nombres_completos" name="nombres_completos" required value="{{ old('nombres_completos') }}" class="auto-next">
                            </div>

                            <div class="register-form-group">
                                <label for="documento_identidad">Documento de Identidad</label>
                                <input type="text" id="documento_identidad" name="documento_identidad" required value="{{ old('documento_identidad') }}" class="auto-next">
                            </div>

                            <div class="register-form-group">
                                <label for="correo">Correo Electrónico</label>
                                <input type="email" id="correo" name="correo" required value="{{ old('correo') }}" class="auto-next">
                            </div>

                            <div class="register-form-group">
                                <label for="password">Contraseña</label>
                                <div class="password-field">
                                    <input type="password" id="password" name="password" required class="auto-next">
                                    <i class="fas fa-eye toggle-password"></i>
                                </div>
                            </div>

                            <div class="register-form-group">
                                <label for="password_confirmation">Confirmar Contraseña</label>
                                <div class="password-field">
                                    <input type="password" id="password_confirmation" name="password_confirmation" required class="auto-next" data-match="password">
                                    <i class="fas fa-eye toggle-password"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección 2: Datos del Programa -->
                    <div class="form-section" data-section="2">
                        <div class="form-grid-compact">
                            <div class="register-form-group">
                                <label for="nombre_programa">Nombre del Programa</label>
                                <input type="text" id="nombre_programa" name="nombre_programa" required value="{{ old('nombre_programa') }}" class="auto-next">
                            </div>

                            <div class="register-form-group">
                                <label for="numero_ficha">Número de Ficha</label>
                                <input type="text" id="numero_ficha" name="numero_ficha" required value="{{ old('numero_ficha') }}" class="auto-next">
                            </div>

                            <div class="register-form-group">
                                <label for="numero_ambiente">Número de Ambiente</label>
                                <input type="text" id="numero_ambiente" name="numero_ambiente" required value="{{ old('numero_ambiente') }}" class="auto-next">
                            </div>

                            <div class="register-form-group">
                                <label for="jornada_id">Jornada</label>
                                <select id="jornada_id" name="jornada_id" required class="auto-next">
                                    <option value="">Selecciona una jornada</option>
                                    @foreach(\App\Models\Jornada::all() as $jornada)
                                        <option value="{{ $jornada->id }}" {{ old('jornada_id') == $jornada->id ? 'selected' : '' }}>
                                            {{ ucfirst($jornada->nombre) }} ({{ \Carbon\Carbon::parse($jornada->hora_entrada)->format('h:i A') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Sección 3: Datos del Dispositivo -->
                    <div class="form-section" data-section="3">
                        <div class="form-grid-compact">
                            <div class="register-form-group">
                                <label for="marca">Marca del Equipo</label>
                                <input type="text" id="marca" name="marca" required value="{{ old('marca') }}" class="auto-next">
                            </div>

                            <div class="register-form-group">
                                <label for="serial">Número de Serial</label>
                                <input type="text" id="serial" name="serial" required value="{{ old('serial') }}" class="auto-next">
                            </div>

                            <div class="file-upload">
                                <label for="foto_serial">
                                    <i class="fas fa-camera"></i>
                                    <span>Subir Foto del Serial</span>
                                </label>
                                <input type="file" id="foto_serial" name="foto_serial" accept="image/*" required>
                                <div class="file-preview" id="file-preview"></div>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="register-alert register-alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Navegación entre secciones (ahora más discreta) -->
                <div class="form-navigation">
                    <button type="button" class="nav-button btn-prev" id="prevBtn" style="display: none;">
                        <i class="fas fa-arrow-left"></i> Anterior
                    </button>
                    <button type="button" class="nav-button btn-next" id="nextBtn">
                        Siguiente <i class="fas fa-arrow-right"></i>
                    </button>
                    <button type="submit" class="register-submit" id="submitBtn" style="display: none;">
                        <i class="fas fa-user-plus"></i> Registrar Aprendiz
                    </button>
                </div>

                <div class="register-links">
                    <p>¿Ya tienes una cuenta? <a href="{{ route('login') }}">Iniciar Sesión</a></p>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentStep = 1;
            const totalSteps = 3;
            const form = document.getElementById('registerForm');
            const nextBtn = document.getElementById('nextBtn');
            const prevBtn = document.getElementById('prevBtn');
            const submitBtn = document.getElementById('submitBtn');
            const progressBar = document.getElementById('progress-bar');
            const passwordFields = document.querySelectorAll('.toggle-password');
            const fileInput = document.getElementById('foto_serial');
            const filePreview = document.getElementById('file-preview');

            // Mostrar vista previa de la imagen
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        filePreview.innerHTML = `<img src="${e.target.result}" alt="Vista previa">`;
                        filePreview.style.display = 'block';
                    }
                    reader.readAsDataURL(this.files[0]);
                    
                    // Si es el último paso, activar el botón de envío
                    if (currentStep === totalSteps) {
                        setTimeout(() => validateAndSubmit(), 500);
                    }
                }
            });

            // Toggle para mostrar/ocultar contraseñas
            passwordFields.forEach(icon => {
                icon.addEventListener('click', function() {
                    const input = this.previousElementSibling;
                    if (input.type === 'password') {
                        input.type = 'text';
                        this.classList.replace('fa-eye', 'fa-eye-slash');
                    } else {
                        input.type = 'password';
                        this.classList.replace('fa-eye-slash', 'fa-eye');
                    }
                });
            });

            // Actualizar la barra de progreso
            function updateProgress() {
                const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
                progressBar.style.width = `${progress}%`;
            }

            // Mostrar sección actual
            function showSection(step) {
                document.querySelectorAll('.form-section').forEach(section => {
                    section.classList.remove('active');
                });
                document.querySelector(`.form-section[data-section="${step}"]`).classList.add('active');

                // Actualizar estados de los pasos
                document.querySelectorAll('.step-item').forEach(item => {
                    const itemStep = parseInt(item.dataset.step);
                    item.classList.remove('active', 'completed');
                    if (itemStep === step) {
                        item.classList.add('active');
                    } else if (itemStep < step) {
                        item.classList.add('completed');
                    }
                });

                // Mostrar/ocultar botones
                prevBtn.style.display = step > 1 ? 'block' : 'none';
                nextBtn.style.display = step < totalSteps ? 'block' : 'none';
                submitBtn.style.display = step === totalSteps ? 'block' : 'none';

                // Enfocar el primer campo de la sección actual
                const firstInput = document.querySelector(`.form-section[data-section="${step}"] input:first-of-type`);
                if (firstInput) setTimeout(() => firstInput.focus(), 300);

                updateProgress();
            }

            // Validar campos de la sección actual
            function validateSection(step) {
                const section = document.querySelector(`.form-section[data-section="${step}"]`);
                const inputs = section.querySelectorAll('input[required]');
                let isValid = true;

                inputs.forEach(input => {
                    // Si es un campo de confirmación de contraseña, validar que coincida
                    if (input.dataset.match) {
                        const matchInput = document.getElementById(input.dataset.match);
                        if (input.value !== matchInput.value) {
                            isValid = false;
                            input.classList.add('error');
                            showTooltip(input, "Las contraseñas no coinciden");
                            return;
                        }
                    }
                    
                    if (!input.value) {
                        isValid = false;
                        input.classList.add('error');
                    } else {
                        input.classList.remove('error');
                    }
                });

                return isValid;
            }
            
            // Mostrar tooltip de error
            function showTooltip(element, message) {
                let tooltip = document.createElement('div');
                tooltip.className = 'error-tooltip';
                tooltip.textContent = message;
                
                // Posicionar tooltip
                element.parentNode.style.position = 'relative';
                element.parentNode.appendChild(tooltip);
                
                // Eliminar después de 3 segundos
                setTimeout(() => {
                    if (tooltip.parentNode) {
                        tooltip.parentNode.removeChild(tooltip);
                    }
                }, 3000);
            }

            // Avanzar al siguiente paso
            function nextStep() {
                if (validateSection(currentStep)) {
                    if (currentStep < totalSteps) {
                        currentStep++;
                        showSection(currentStep);
                    } else {
                        validateAndSubmit();
                    }
                }
            }

            // Validar y enviar formulario
            function validateAndSubmit() {
                if (validateSection(totalSteps)) {
                    // Efecto visual para el botón de envío
                    submitBtn.classList.add('submitting');
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
                    
                    // Enviar formulario después de una breve animación
                    setTimeout(() => form.submit(), 800);
                }
            }

            // Event listeners
            nextBtn.addEventListener('click', nextStep);

            prevBtn.addEventListener('click', () => {
                currentStep--;
                showSection(currentStep);
            });

            // Auto-avance al llenar campos
            document.querySelectorAll('.auto-next').forEach(input => {
                let timeoutId;
                
                input.addEventListener('input', function() {
                    clearTimeout(timeoutId);
                    
                    // Si es el último campo de la sección actual, verificar si está completo
                    const isLastInSection = Array.from(document.querySelectorAll(`.form-section[data-section="${currentStep}"] .auto-next`))
                        .pop() === this;
                        
                    if (isLastInSection && this.value.trim() !== '') {
                        timeoutId = setTimeout(() => {
                            if (validateSection(currentStep)) {
                                nextStep();
                            }
                        }, 800); // Esperar 800ms para avanzar automáticamente
                    }
                });
                
                // Avanzar al presionar Enter
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        nextStep();
                    }
                });
            });

            // Click en los pasos (navegación)
            document.querySelectorAll('.step-item').forEach(item => {
                item.addEventListener('click', () => {
                    const step = parseInt(item.dataset.step);
                    // Sólo permitir ir a pasos que ya se han completado o al actual
                    if (step <= currentStep) {
                        currentStep = step;
                        showSection(currentStep);
                    } else if (validateSection(currentStep)) {
                        // Si intenta ir a un paso futuro, validar el actual primero
                        currentStep = step;
                        showSection(currentStep);
                    }
                });
            });

            // Inicializar
            showSection(1);
        });
    </script>
</body>
</html>