<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.



## 📁 Estructura del Proyecto

```text
msa_sistema_facturacion/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # Controladores (Auth, Clientes, Proveedores, Pagos, Reportes, Dashboard, Sunat)
│   │   └── Middleware/           # Middlewares personalizados (ej. SetActiveEmpresaSede)
│   ├── Models/                   # Modelos Eloquent (Empresa, Sede, Cliente, Proveedor, Comprobantes, Pagos, etc.)
│   ├── Providers/                # Service Providers de la aplicación
│   └── Services/                 # Capa de servicios (DashboardService, PagoService, SunatService, WhatsAppService)
├── bootstrap/                    # Configuración de arranque de Laravel
├── config/                       # Archivos de configuración (app, auth, database, services, etc.)
├── database/
│   ├── factories/                # Fábricas de modelos
│   ├── migrations/               # Migraciones de base de datos (Estructura Multi-empresa y Multi-sede)
│   └── seeders/                  # Seeders con datos iniciales
├── documentacion/                # Especificación técnica, arquitectura, diseño y guías del sistema
│   ├── 01_REQUERIMIENTOS_Y_ALCANCE.md
│   ├── 02_ARQUITECTURA_Y_DISENO_TECNICO.md
│   ├── 03_MODELO_DE_DATOS_Y_SQL.md
│   ├── 04_MODULOS_Y_PANTALLAS.md
│   ├── 05_INTEGRACIONES_Y_ALERTAS.md
│   └── README.md
├── public/                       # Punto de entrada web y recursos públicos (CSS, JS, favicon)
│   ├── css/                      # Estilos personalizados (app.css, auth.css)
│   └── js/                       # Scripts del frontend (app.js, auth.js, clientes.js, modal-abonos.js, etc.)
├── resources/
│   ├── css/                      # Estilos fuentes
│   ├── js/                       # JavaScript fuente
│   └── views/                    # Vistas Blade del sistema
│       ├── auth/                 # Login y autenticación
│       ├── clientes/             # Vistas de gestión de clientes
│       ├── components/           # Componentes Blade reutilizables (ej. modal_abonos)
│       ├── dashboard/            # Panel principal con KPIs e indicadores
│       ├── layouts/              # Plantillas base (app, navbar, sidebar)
│       ├── proveedores/          # Vistas de gestión de proveedores
│       └── reportes/             # Reportes de cuentas por cobrar y por pagar
├── routes/
│   ├── console.php               # Comandos de consola
│   └── web.php                   # Definición de rutas web y endpoints de la aplicación
├── storage/                      # Almacenamiento local, logs y caché del framework
├── tests/                        # Pruebas automatizadas (Feature y Unit)
├── .env.example                  # Plantilla de variables de entorno
├── composer.json                 # Dependencias PHP (Laravel Framework)
├── package.json                  # Dependencias de frontend y scripts de compilación
└── vite.config.js                # Configuración del bundler Vite
```

### 📋 Descripción de Módulos Principales

| Módulo / Directorio | Descripción |
| :--- | :--- |
| **`app/Services/`** | Lógica de negocio desacoplada: consultas SUNAT (RUC/DNI), lógica financiera de pagos/abonos, métricas del dashboard y notificaciones por WhatsApp. |
| **`app/Http/Controllers/`** | Controladores que orquestan las peticiones HTTP, validaciones y respuestas (JSON/Blade). |
| **`app/Models/`** | Modelos relacionales con soporte para arquitectura Multi-Empresa y Multi-Sede. |
| **`database/migrations/`** | Esquema relacional optimizado para facturación, compras, ventas y amortizaciones. |
| **`resources/views/`** | Interfaz de usuario construida con Blade y diseño modular responsivo. |
| **`documentacion/`** | Documentación exhaustiva sobre arquitectura, requerimientos, modelo SQL e integraciones. |
