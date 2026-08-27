<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Iniciar Sesión | MSA Facturación</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom Auth CSS -->
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <div class="brand-icon-lg">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
            <h4 class="fw-bold text-dark mb-1">MSA Facturación</h4>
            <p class="text-muted small mb-0">Sistema de Control Contable y Cobranzas Multi-Sede</p>
        </div>

        <div class="login-body">
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show small py-2">
                    <i class="fa-solid fa-circle-exclamation me-1"></i> {{ $errors->first() }}
                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('login.submit') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="username" class="form-label small fw-bold text-muted">USUARIO</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-user"></i></span>
                        <input type="text" name="username" id="username" class="form-control border-start-0 ps-0" placeholder="Ingrese su usuario" value="{{ old('username', 'admin') }}" required autofocus autocomplete="username">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label small fw-bold text-muted">CONTRASEÑA</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="password" id="password" class="form-control border-start-0 ps-0" placeholder="••••••••" value="admin123" required autocomplete="current-password">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 btn-submit fw-bold shadow-sm">
                    <i class="fa-solid fa-arrow-right-to-bracket me-2"></i> Ingresar al Sistema
                </button>
            </form>

            <div class="mt-4 pt-3 border-top text-center">
                <div class="text-muted small mb-2 fw-semibold">Acceso rápido para demostración:</div>
                <div class="d-flex justify-content-center gap-2 demo-buttons">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDemo('admin', 'admin123')">
                        <i class="fa-solid fa-user-shield me-1"></i> Admin
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDemo('contador', 'contador123')">
                        <i class="fa-solid fa-calculator me-1"></i> Contador
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom Auth JS -->
<script src="{{ asset('js/auth.js') }}"></script>
</body>
</html>
