<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Sede;
use App\Models\User;
use App\Models\ComprobanteCompra;
use App\Models\ComprobanteVenta;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class EmpresaController extends Controller
{
    /**
     * Listado general de empresas con estadísticas
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Empresa::withCount(['sedes', 'comprobantesCompras', 'comprobantesVentas']);

        // Si no es superadmin, listar solo las empresas asignadas
        if ($user && !$user->isSuperAdmin()) {
            $query->whereIn('id', $user->empresas()->pluck('empresas.id'));
        }

        // Filtro de búsqueda por texto (RUC, Razón Social, Nombre Comercial)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('ruc', 'like', "%{$search}%")
                  ->orWhere('razon_social', 'like', "%{$search}%")
                  ->orWhere('nombre_comercial', 'like', "%{$search}%")
                  ->orWhere('ciudad', 'like', "%{$search}%")
                  ->orWhere('telefono', 'like', "%{$search}%");
            });
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            if ($request->estado === 'activo') {
                $query->where('activo', true);
            } elseif ($request->estado === 'inactivo') {
                $query->where('activo', false);
            }
        }

        $empresas = $query->orderBy('razon_social', 'asc')->get();

        // Métricas de resumen
        $totalEmpresas = $empresas->count();
        $totalEmpresasActivas = $empresas->where('activo', true)->count();
        $totalSedes = $empresas->sum('sedes_count');

        return view('empresas.index', compact(
            'empresas',
            'totalEmpresas',
            'totalEmpresasActivas',
            'totalSedes'
        ));
    }

    /**
     * Formulario de creación de nueva empresa
     */
    public function create()
    {
        return view('empresas.create');
    }

    /**
     * Almacenar nueva empresa en la base de datos
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ruc' => 'required|string|size:11|unique:empresas,ruc',
            'razon_social' => 'required|string|max:255',
            'nombre_comercial' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:50',
            'correo' => 'nullable|email|max:150',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'cuentas_bancarias' => 'nullable|string',
            'dias_alerta_vencimiento' => 'required|integer|min:1|max:60',
            'activo' => 'nullable|boolean',
            'crear_sede_principal' => 'nullable|boolean',
            'nombre_sede_principal' => 'nullable|string|max:150',
        ], [
            'ruc.required' => 'El número de RUC es obligatorio.',
            'ruc.size' => 'El RUC debe tener exactamente 11 dígitos.',
            'ruc.unique' => 'Ya existe una empresa registrada con este número de RUC.',
            'razon_social.required' => 'La Razón Social es obligatoria.',
            'correo.email' => 'Ingrese un formato de correo electrónico válido.',
            'logo.image' => 'El archivo seleccionado debe ser una imagen válida.',
            'logo.max' => 'La imagen del logo no debe superar los 2MB.',
        ]);

        $logoUrl = null;
        if ($request->hasFile('logo')) {
            $logoFile = $request->file('logo');
            $logoFileName = 'empresa_' . $validated['ruc'] . '_' . time() . '.' . $logoFile->getClientOriginalExtension();
            $destinationPath = public_path('uploads/logos');
            
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }
            
            $logoFile->move($destinationPath, $logoFileName);
            $logoUrl = 'uploads/logos/' . $logoFileName;
        }

        $empresa = Empresa::create([
            'ruc' => $validated['ruc'],
            'razon_social' => mb_strtoupper($validated['razon_social'], 'UTF-8'),
            'nombre_comercial' => $validated['nombre_comercial'] ? mb_strtoupper($validated['nombre_comercial'], 'UTF-8') : null,
            'direccion' => $validated['direccion'],
            'telefono' => $validated['telefono'],
            'correo' => $validated['correo'] ? strtolower($validated['correo']) : null,
            'logo_url' => $logoUrl,
            'cuentas_bancarias' => $validated['cuentas_bancarias'],
            'dias_alerta_vencimiento' => $validated['dias_alerta_vencimiento'] ?? 5,
            'activo' => $request->has('activo') ? $request->boolean('activo') : true,
        ]);

        // Crear sede principal automáticamente si está habilitada la opción
        $sedePrincipal = null;
        if ($request->boolean('crear_sede_principal', true)) {
            $nombreSede = $request->filled('nombre_sede_principal') 
                ? $request->input('nombre_sede_principal') 
                : 'Sede Principal - ' . ($empresa->nombre_comercial ?? 'Central');

            $sedePrincipal = Sede::create([
                'empresa_id' => $empresa->id,
                'nombre' => $nombreSede,
                'codigo' => 'SED-01',
                'direccion' => $empresa->direccion,
                'telefono' => $empresa->telefono,
                'ciudad' => 'Lima',
                'activo' => true,
            ]);
        }

        // Asignar automáticamente los permisos al usuario actual y a los Superadministradores
        $user = Auth::user();
        if ($user) {
            $user->empresas()->syncWithoutDetaching([$empresa->id]);
            if ($sedePrincipal) {
                $user->sedes()->syncWithoutDetaching([$sedePrincipal->id]);
            }
        }

        $superadmins = User::where('rol', 'SUPERADMIN')->get();
        foreach ($superadmins as $sa) {
            $sa->empresas()->syncWithoutDetaching([$empresa->id]);
            if ($sedePrincipal) {
                $sa->sedes()->syncWithoutDetaching([$sedePrincipal->id]);
            }
        }

        return redirect()->route('empresas.show', $empresa->id)
            ->with('success', "Empresa '{$empresa->razon_social}' creada exitosamente." . ($sedePrincipal ? " Se generó su '{$sedePrincipal->nombre}'." : ""));
    }

    /**
     * Vista de detalle de una empresa con sus sedes y métricas
     */
    public function show($id)
    {
        $empresa = Empresa::with(['sedes' => function ($query) {
            $query->withCount(['comprobantesCompras', 'comprobantesVentas'])->orderBy('nombre', 'asc');
        }])->withCount(['comprobantesCompras', 'comprobantesVentas'])->findOrFail($id);

        $user = Auth::user();
        if ($user && !$user->isSuperAdmin() && !$user->empresas()->where('empresas.id', $empresa->id)->exists()) {
            return redirect()->route('empresas.index')->with('error', 'No tiene permisos para ver esta empresa.');
        }

        $totalComprasMonto = ComprobanteCompra::where('empresa_id', $empresa->id)->sum('monto_total');
        $totalVentasMonto = ComprobanteVenta::where('empresa_id', $empresa->id)->sum('monto_total');

        return view('empresas.show', compact('empresa', 'totalComprasMonto', 'totalVentasMonto'));
    }

    /**
     * Formulario de edición de empresa
     */
    public function edit($id)
    {
        $empresa = Empresa::findOrFail($id);

        $user = Auth::user();
        if ($user && !$user->isSuperAdmin() && !$user->empresas()->where('empresas.id', $empresa->id)->exists()) {
            return redirect()->route('empresas.index')->with('error', 'No tiene permisos para editar esta empresa.');
        }

        return view('empresas.edit', compact('empresa'));
    }

    /**
     * Actualizar información de una empresa existente
     */
    public function update(Request $request, $id)
    {
        $empresa = Empresa::findOrFail($id);

        $user = Auth::user();
        if ($user && !$user->isSuperAdmin() && !$user->empresas()->where('empresas.id', $empresa->id)->exists()) {
            return redirect()->route('empresas.index')->with('error', 'No tiene permisos para modificar esta empresa.');
        }

        $validated = $request->validate([
            'ruc' => 'required|string|size:11|unique:empresas,ruc,' . $empresa->id,
            'razon_social' => 'required|string|max:255',
            'nombre_comercial' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:50',
            'correo' => 'nullable|email|max:150',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'cuentas_bancarias' => 'nullable|string',
            'dias_alerta_vencimiento' => 'required|integer|min:1|max:60',
            'activo' => 'nullable|boolean',
        ], [
            'ruc.required' => 'El número de RUC es obligatorio.',
            'ruc.size' => 'El RUC debe tener exactamente 11 dígitos.',
            'ruc.unique' => 'Ya existe otra empresa registrada con este número de RUC.',
            'razon_social.required' => 'La Razón Social es obligatoria.',
            'correo.email' => 'Ingrese un formato de correo electrónico válido.',
            'logo.image' => 'El archivo seleccionado debe ser una imagen válida.',
            'logo.max' => 'La imagen del logo no debe superar los 2MB.',
        ]);

        if ($request->hasFile('logo')) {
            // Eliminar logo anterior si existe
            if ($empresa->logo_url && File::exists(public_path($empresa->logo_url))) {
                File::delete(public_path($empresa->logo_url));
            }

            $logoFile = $request->file('logo');
            $logoFileName = 'empresa_' . $validated['ruc'] . '_' . time() . '.' . $logoFile->getClientOriginalExtension();
            $destinationPath = public_path('uploads/logos');
            
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }
            
            $logoFile->move($destinationPath, $logoFileName);
            $empresa->logo_url = 'uploads/logos/' . $logoFileName;
        }

        $empresa->ruc = $validated['ruc'];
        $empresa->razon_social = mb_strtoupper($validated['razon_social'], 'UTF-8');
        $empresa->nombre_comercial = $validated['nombre_comercial'] ? mb_strtoupper($validated['nombre_comercial'], 'UTF-8') : null;
        $empresa->direccion = $validated['direccion'];
        $empresa->telefono = $validated['telefono'];
        $empresa->correo = $validated['correo'] ? strtolower($validated['correo']) : null;
        $empresa->cuentas_bancarias = $validated['cuentas_bancarias'];
        $empresa->dias_alerta_vencimiento = $validated['dias_alerta_vencimiento'];
        $empresa->activo = $request->has('activo') ? $request->boolean('activo') : false;
        $empresa->save();

        return redirect()->route('empresas.show', $empresa->id)
            ->with('success', "Empresa '{$empresa->razon_social}' actualizada exitosamente.");
    }

    /**
     * Alternar estado Activo / Inactivo
     */
    public function toggleStatus($id)
    {
        $empresa = Empresa::findOrFail($id);
        $empresa->activo = !$empresa->activo;
        $empresa->save();

        $estadoStr = $empresa->activo ? 'Activada' : 'Desactivada';
        return back()->with('info', "Empresa '{$empresa->razon_social}' ha sido {$estadoStr}.");
    }

    /**
     * Eliminar empresa con validación de transacciones contables
     */
    public function destroy($id)
    {
        $empresa = Empresa::findOrFail($id);

        $comprasCount = ComprobanteCompra::where('empresa_id', $empresa->id)->count();
        $ventasCount = ComprobanteVenta::where('empresa_id', $empresa->id)->count();

        if ($comprasCount > 0 || $ventasCount > 0) {
            return back()->with('error', "No es posible eliminar la empresa '{$empresa->razon_social}' porque cuenta con {$comprasCount} compras y {$ventasCount} ventas registradas. Para ocultarla de las operaciones, puede Desactivarla.");
        }

        // Si la empresa activa en sesión es la eliminada, limpiar sesión
        if (session('active_empresa_id') == $empresa->id) {
            session()->forget(['active_empresa_id', 'active_sede_id']);
        }

        // Eliminar logo si existe
        if ($empresa->logo_url && File::exists(public_path($empresa->logo_url))) {
            File::delete(public_path($empresa->logo_url));
        }

        // Desvincular usuarios y sedes
        $empresa->users()->detach();
        $empresa->sedes()->delete();
        $empresa->delete();

        return redirect()->route('empresas.index')
            ->with('success', "La empresa '{$empresa->razon_social}' y sus sedes han sido eliminadas correctamente.");
    }
}
