<header class="top-navbar d-flex align-items-center justify-content-between">
    <!-- Left: Mobile Toggle & Context Selectors -->
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-sm btn-outline-secondary d-lg-none" id="sidebarToggle">
            <i class="fa-solid fa-bars"></i>
        </button>

        <!-- Selector de Empresa Activa -->
        <form action="{{ route('context.set_empresa') }}" method="POST" class="d-flex align-items-center">
            @csrf
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light text-primary border-end-0 fw-bold">
                    <i class="fa-solid fa-building me-1"></i> Empresa:
                </span>
                <select name="empresa_id" class="form-select form-select-sm fw-semibold border-start-0 text-truncate" style="max-width: 250px;" onchange="this.form.submit()">
                    <option value="all" {{ is_null($activeEmpresaId) ? 'selected' : '' }}>🌐 Todas las Empresas (Global)</option>
                    @foreach($empresasDisponibles as $emp)
                        <option value="{{ $emp->id }}" {{ $activeEmpresaId == $emp->id ? 'selected' : '' }}>
                            {{ $emp->nombre_comercial ?? $emp->razon_social }} ({{ $emp->ruc }})
                        </option>
                    @endforeach
                </select>
            </div>
        </form>

        <!-- Selector de Sede Activa (Si hay empresa seleccionada) -->
        @if($activeEmpresaId)
            <form action="{{ route('context.set_sede') }}" method="POST" class="d-flex align-items-center">
                @csrf
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-success border-end-0 fw-bold">
                        <i class="fa-solid fa-location-dot me-1"></i> Sede:
                    </span>
                    <select name="sede_id" class="form-select form-select-sm fw-semibold border-start-0" style="max-width: 220px;" onchange="this.form.submit()">
                        <option value="all" {{ is_null($activeSedeId) ? 'selected' : '' }}>🌐 Todas las Sedes</option>
                        @foreach($sedesDisponibles as $sed)
                            <option value="{{ $sed->id }}" {{ $activeSedeId == $sed->id ? 'selected' : '' }}>
                                {{ $sed->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        @endif
    </div>

    <!-- Right: Date & User Profile -->
    <div class="d-flex align-items-center gap-3">
        <div class="d-none d-md-flex align-items-center text-muted small">
            <i class="fa-regular fa-calendar me-1 text-primary"></i>
            {{ \Carbon\Carbon::now()->isoFormat('D [de] MMMM [de] YYYY') }}
        </div>

        <div class="dropdown">
            <button class="btn btn-sm btn-light border dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 0.8rem; font-weight: 700;">
                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                </div>
                <span class="fw-semibold small d-none d-sm-inline">{{ Auth::user()->name ?? 'Usuario Contable' }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li class="dropdown-header">
                    <div class="fw-bold text-dark">{{ Auth::user()->name ?? 'Usuario' }}</div>
                    <small class="text-muted">{{ Auth::user()->rol ?? 'CONTADOR' }}</small>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
                        <i class="fa-solid fa-gauge text-muted"></i> Dashboard
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar Sesión
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
