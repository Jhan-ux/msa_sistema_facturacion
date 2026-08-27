<aside class="sidebar">
    <a href="{{ route('dashboard') }}" class="sidebar-brand">
        <div class="brand-icon">
            <i class="fa-solid fa-file-invoice-dollar"></i>
        </div>
        <div>
            <div class="fw-bold fs-5 tracking-tight">MSA FACT</div>
            <div style="font-size: 0.7rem; color: #94a3b8; letter-spacing: 0.05em;">CONTABILIDAD & CXP/CXC</div>
        </div>
    </a>

    <ul class="sidebar-menu">
        <li class="sidebar-heading">Principal</li>
        <li class="sidebar-item">
            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Dashboard & Alertas</span>
            </a>
        </li>

        <li class="sidebar-heading">Cuentas por Pagar (Compras)</li>
        <li class="sidebar-item">
            <a href="{{ route('proveedores.index') }}" class="sidebar-link {{ request()->routeIs('proveedores.index') ? 'active' : '' }}">
                <i class="fa-solid fa-truck-field"></i>
                <span>Proveedores (CxP)</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="{{ route('proveedores.create') }}" class="sidebar-link {{ request()->routeIs('proveedores.create') ? 'active' : '' }}">
                <i class="fa-solid fa-plus-circle"></i>
                <span>Nueva Factura Compra</span>
            </a>
        </li>

        <li class="sidebar-heading">Cuentas por Cobrar (Ventas)</li>
        <li class="sidebar-item">
            <a href="{{ route('clientes.index') }}" class="sidebar-link {{ request()->routeIs('clientes.index') ? 'active' : '' }}">
                <i class="fa-solid fa-users"></i>
                <span>Clientes (CxC)</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="{{ route('clientes.create') }}" class="sidebar-link {{ request()->routeIs('clientes.create') ? 'active' : '' }}">
                <i class="fa-solid fa-file-circle-plus"></i>
                <span>Nueva Factura Venta</span>
            </a>
        </li>

        <li class="sidebar-heading">Reportes & Estados</li>
        <li class="sidebar-item">
            <a href="{{ route('reportes.cxp') }}" class="sidebar-link {{ request()->routeIs('reportes.cxp') ? 'active' : '' }}">
                <i class="fa-solid fa-file-excel text-danger"></i>
                <span>Reporte Proveedores</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="{{ route('reportes.cxc') }}" class="sidebar-link {{ request()->routeIs('reportes.cxc') ? 'active' : '' }}">
                <i class="fa-solid fa-file-pdf text-success"></i>
                <span>Reporte Clientes</span>
            </a>
        </li>

        <li class="sidebar-heading">Cuenta & Configuración</li>
        <li class="sidebar-item">
            <a href="{{ route('perfil.show') }}" class="sidebar-link {{ request()->routeIs('perfil.show') ? 'active' : '' }}">
                <i class="fa-solid fa-user-gear text-info"></i>
                <span>Mi Perfil & Seguridad</span>
            </a>
        </li>
    </ul>
</aside>
