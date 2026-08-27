@extends('layouts.app')

@section('title', 'Mi Perfil de Usuario')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted"><i class="fa-solid fa-gauge me-1"></i> Dashboard</a></li>
                <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Mi Perfil</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-user-gear text-primary me-2"></i> Mi Perfil y Seguridad
        </h4>
    </div>
    <div>
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver al Dashboard
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Columna Izquierda: Tarjeta de Identidad y Accesos -->
    <div class="col-12 col-lg-4">
        <!-- Tarjeta de Perfil -->
        <div class="msa-card mb-4 text-center">
            <div class="msa-card-body pt-4 pb-4">
                <!-- Avatar Circular Grande -->
                <div class="mx-auto mb-3 position-relative" style="width: 90px; height: 90px;">
                    <div class="w-100 h-100 rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow" style="font-size: 2.2rem; font-weight: 800; background: linear-gradient(135deg, #1e3a8a, #3b82f6) !important;">
                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                    </div>
                    <span class="position-absolute bottom-0 end-0 p-2 bg-success border border-white rounded-circle" title="Usuario Activo"></span>
                </div>

                <h5 class="fw-bold text-dark mb-1">{{ $user->name }}</h5>
                <p class="text-muted small mb-2"><i class="fa-solid fa-at text-primary me-1"></i>{{ $user->username }}</p>
                
                <div class="d-flex justify-content-center gap-1 mb-3">
                    @if($user->rol === 'SUPERADMIN')
                        <span class="badge bg-danger px-3 py-2"><i class="fa-solid fa-shield-halved me-1"></i> SUPERADMINISTRADOR</span>
                    @elseif($user->rol === 'ADMIN_EMPRESA')
                        <span class="badge bg-primary px-3 py-2"><i class="fa-solid fa-user-tie me-1"></i> ADMINISTRADOR</span>
                    @elseif($user->rol === 'CONTADOR')
                        <span class="badge bg-info text-dark px-3 py-2"><i class="fa-solid fa-calculator me-1"></i> CONTADOR GENERAL</span>
                    @else
                        <span class="badge bg-secondary px-3 py-2"><i class="fa-solid fa-user me-1"></i> {{ $user->rol }}</span>
                    @endif
                </div>

                <hr class="my-3 text-muted">

                <div class="text-start small">
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted"><i class="fa-regular fa-envelope me-2 text-primary"></i> Correo:</span>
                        <span class="fw-semibold text-dark">{{ $user->email }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted"><i class="fa-solid fa-circle-check me-2 text-success"></i> Estado:</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle fw-semibold">Activo</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted"><i class="fa-regular fa-calendar-check me-2 text-info"></i> Registrado:</span>
                        <span class="text-muted">{{ $user->created_at ? $user->created_at->format('d/m/Y') : 'N/D' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted"><i class="fa-regular fa-clock me-2 text-warning"></i> Actualizado:</span>
                        <span class="text-muted">{{ $user->updated_at ? $user->updated_at->diffForHumans() : 'N/D' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta de Empresas y Sedes Asignadas -->
        <div class="msa-card">
            <div class="msa-card-header bg-light">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-building-user text-primary"></i>
                    <span class="fw-bold">Empresas & Sedes con Acceso</span>
                </div>
            </div>
            <div class="msa-card-body p-3">
                @if($user->isSuperAdmin())
                    <div class="alert alert-info py-2 px-3 small mb-0 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-crown text-primary fs-5"></i>
                        <div>
                            <strong>Acceso Global Total</strong>
                            <div>Como Superadmin tienes acceso irrestricto a todas las empresas y sedes.</div>
                        </div>
                    </div>
                @else
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-1">Empresas Asignadas:</label>
                        @forelse($user->empresas as $emp)
                            <div class="d-flex align-items-center gap-2 mb-2 p-2 rounded bg-light border">
                                <i class="fa-solid fa-building text-primary"></i>
                                <div>
                                    <div class="fw-bold small text-dark">{{ $emp->nombre_comercial ?? $emp->razon_social }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">RUC: {{ $emp->ruc }}</div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">Sin empresas asignadas directamente.</p>
                        @endforelse
                    </div>

                    <div>
                        <label class="form-label small fw-bold text-muted text-uppercase mb-1">Sedes Asignadas:</label>
                        @forelse($user->sedes as $sed)
                            <div class="d-flex align-items-center gap-2 mb-1 p-2 rounded bg-light border">
                                <i class="fa-solid fa-location-dot text-success"></i>
                                <div class="small fw-semibold text-dark">{{ $sed->nombre }}</div>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">Sin sedes asignadas directamente.</p>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Columna Derecha: Formularios de Edición y Contraseña -->
    <div class="col-12 col-lg-8">
        <!-- Tarjeta 1: Actualizar Datos de Perfil -->
        <div class="msa-card mb-4">
            <div class="msa-card-header bg-light">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-id-card text-primary fs-5"></i>
                    <div>
                        <div class="fw-bold">Información Personal</div>
                        <small class="text-muted fw-normal">Actualiza tus datos de identificación y contacto</small>
                    </div>
                </div>
            </div>
            <div class="msa-card-body">
                <form action="{{ route('perfil.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="name" class="form-label fw-semibold text-dark">
                                <i class="fa-solid fa-user me-1 text-primary"></i> Nombre Completo <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $user->name) }}" 
                                   required 
                                   placeholder="Ej: Juan Pérez">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="username" class="form-label fw-semibold text-dark">
                                <i class="fa-solid fa-user-tag me-1 text-primary"></i> Nombre de Usuario <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('username') is-invalid @enderror" 
                                   id="username" 
                                   name="username" 
                                   value="{{ old('username', $user->username) }}" 
                                   required 
                                   placeholder="Ej: jperez">
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text small">Usado para iniciar sesión en el sistema.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="email" class="form-label fw-semibold text-dark">
                                <i class="fa-solid fa-envelope me-1 text-primary"></i> Correo Electrónico <span class="text-danger">*</span>
                            </label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $user->email) }}" 
                                   required 
                                   placeholder="ejemplo@msa.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark">
                                <i class="fa-solid fa-user-shield me-1 text-primary"></i> Rol Asignado
                            </label>
                            <input type="text" class="form-control bg-light text-muted" value="{{ $user->rol }}" readonly disabled>
                            <div class="form-text small">El rol solo puede ser modificado por un Administrador.</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end pt-2 border-top">
                        <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                            <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios de Perfil
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tarjeta 2: Cambiar Contraseña -->
        <div class="msa-card">
            <div class="msa-card-header bg-light">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-lock text-danger fs-5"></i>
                    <div>
                        <div class="fw-bold">Seguridad y Cambio de Contraseña</div>
                        <small class="text-muted fw-normal">Se recomienda usar contraseñas seguras de al menos 6 caracteres</small>
                    </div>
                </div>
            </div>
            <div class="msa-card-body">
                <form action="{{ route('perfil.password') }}" method="POST" id="passwordChangeForm">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-3">
                        <!-- Contraseña Actual -->
                        <div class="col-12">
                            <label for="current_password" class="form-label fw-semibold text-dark">
                                Contraseña Actual <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-key text-muted"></i></span>
                                <input type="password" 
                                       class="form-control @error('current_password') is-invalid @enderror" 
                                       id="current_password" 
                                       name="current_password" 
                                       required 
                                       placeholder="Ingresa tu contraseña actual">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="current_password">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Nueva Contraseña -->
                        <div class="col-12 col-md-6">
                            <label for="password" class="form-label fw-semibold text-dark">
                                Nueva Contraseña <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-lock text-muted"></i></span>
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       required 
                                       minlength="6"
                                       placeholder="Mínimo 6 caracteres">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text small">Debe contener al menos 6 caracteres.</div>
                        </div>

                        <!-- Confirmar Nueva Contraseña -->
                        <div class="col-12 col-md-6">
                            <label for="password_confirmation" class="form-label fw-semibold text-dark">
                                Confirmar Nueva Contraseña <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-lock-open text-muted"></i></span>
                                <input type="password" 
                                       class="form-control" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       required 
                                       minlength="6"
                                       placeholder="Repite la nueva contraseña">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_confirmation">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                            <div id="passwordMatchMessage" class="small mt-1"></div>
                        </div>
                    </div>

                    <!-- Caja informativa de buenas prácticas -->
                    <div class="alert alert-light border small text-muted mb-3 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-circle-info text-primary fs-5"></i>
                        <div>
                            Consejo de seguridad: Utiliza una combinación de letras mayúsculas, minúsculas, números y símbolos para mayor protección.
                        </div>
                    </div>

                    <div class="d-flex justify-content-end pt-2 border-top">
                        <button type="submit" class="btn btn-danger d-flex align-items-center gap-2" id="btnSubmitPassword">
                            <i class="fa-solid fa-key"></i> Actualizar Contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Alternar visibilidad de campos de contraseña (ojito)
    const toggleButtons = document.querySelectorAll('.toggle-password');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Validación visual de coincidencia de contraseñas
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');
    const matchMessage = document.getElementById('passwordMatchMessage');

    function checkPasswordMatch() {
        const pass = passwordInput.value;
        const confirm = confirmInput.value;

        if (confirm.length === 0) {
            matchMessage.textContent = '';
            matchMessage.className = 'small mt-1';
            return;
        }

        if (pass === confirm) {
            matchMessage.innerHTML = '<span class="text-success fw-semibold"><i class="fa-solid fa-check me-1"></i> Las contraseñas coinciden</span>';
        } else {
            matchMessage.innerHTML = '<span class="text-danger fw-semibold"><i class="fa-solid fa-xmark me-1"></i> Las contraseñas no coinciden</span>';
        }
    }

    if (passwordInput && confirmInput) {
        passwordInput.addEventListener('input', checkPasswordMatch);
        confirmInput.addEventListener('input', checkPasswordMatch);
    }
});
</script>
@endpush
