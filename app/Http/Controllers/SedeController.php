<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sede;
use App\Models\Empresa;
use App\Models\User;
use App\Models\ComprobanteCompra;
use App\Models\ComprobanteVenta;
use Illuminate\Support\Facades\Auth;

class SedeController extends Controller
{
    /**
     * Listado general de sedes con filtros por empresa y estado
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Empresas disponibles para el usuario
        if ($user && $user->isSuperAdmin()) {
            $empresas = Empresa::orderBy('razon_social', 'asc')->get();
        } elseif ($user) {
            $empresas = $user->empresas()->orderBy('razon_social', 'asc')->get();
        } else {
            $empresas = Empresa::orderBy('razon_social', 'asc')->get();
        }

        $query = Sede::with('empresa')->withCount(['comprobantesCompras', 'comprobantesVentas']);

        // Si no es superadmin, restringir a las sedes/empresas que le pertenecen
        if ($user && !$user->isSuperAdmin()) {
            $query->whereIn('empresa_id', $empresas->pluck('id'));
        }

        // Filtro por Empresa
        if ($request->filled('empresa_id') && $request->empresa_id !== 'all') {
            $query->where('empresa_id', $request->empresa_id);
        }

        // Filtro por Estado
        if ($request->filled('estado')) {
            if ($request->estado === 'activo') {
                $query->where('activo', true);
            } elseif ($request->estado === 'inactivo') {
                $query->where('activo', false);
            }
        }

        // Búsqueda por texto (nombre, código, ciudad, dirección, teléfono)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo', 'like', "%{$search}%")
                  ->orWhere('ciudad', 'like', "%{$search}%")
                  ->orWhere('direccion', 'like', "%{$search}%")
                  ->orWhere('telefono', 'like', "%{$search}%");
            });
        }

        $sedes = $query->orderBy('empresa_id', 'asc')->orderBy('nombre', 'asc')->get();

        $totalSedes = $sedes->count();
        $totalSedesActivas = $sedes->where('activo', true)->count();

        return view('sedes.index', compact(
            'sedes',
            'empresas',
            'totalSedes',
            'totalSedesActivas'
        ));
    }

    /**
     * Formulario de creación de nueva sede
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        if ($user && $user->isSuperAdmin()) {
            $empresas = Empresa::where('activo', true)->orderBy('razon_social', 'asc')->get();
        } elseif ($user) {
            $empresas = $user->empresas()->where('activo', true)->orderBy('razon_social', 'asc')->get();
        } else {
            $empresas = Empresa::where('activo', true)->orderBy('razon_social', 'asc')->get();
        }

        $selectedEmpresaId = $request->query('empresa_id', session('active_empresa_id'));

        return view('sedes.create', compact('empresas', 'selectedEmpresaId'));
    }

    /**
     * Almacenar nueva sede en la base de datos
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'nombre' => 'required|string|max:150',
            'codigo' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:50',
            'activo' => 'nullable|boolean',
        ], [
            'empresa_id.required' => 'Debe seleccionar una empresa para asociar la sede.',
            'empresa_id.exists' => 'La empresa seleccionada no es válida.',
            'nombre.required' => 'El nombre de la sede es obligatorio.',
        ]);

        $sede = Sede::create([
            'empresa_id' => $validated['empresa_id'],
            'nombre' => $validated['nombre'],
            'codigo' => $validated['codigo'] ? strtoupper($validated['codigo']) : null,
            'direccion' => $validated['direccion'],
            'ciudad' => $validated['ciudad'] ?? 'Lima',
            'telefono' => $validated['telefono'],
            'activo' => $request->has('activo') ? $request->boolean('activo') : true,
        ]);

        // Asignar permisos a la sede creada
        $user = Auth::user();
        if ($user) {
            $user->sedes()->syncWithoutDetaching([$sede->id]);
        }

        $superadmins = User::where('rol', 'SUPERADMIN')->get();
        foreach ($superadmins as $sa) {
            $sa->sedes()->syncWithoutDetaching([$sede->id]);
        }

        // Si viene del detalle de una empresa, volver allí, sino a la lista de sedes
        if ($request->has('redirect_to_empresa')) {
            return redirect()->route('empresas.show', $sede->empresa_id)
                ->with('success', "Sede '{$sede->nombre}' creada exitosamente.");
        }

        return redirect()->route('sedes.index', ['empresa_id' => $sede->empresa_id])
            ->with('success', "Sede '{$sede->nombre}' registrada con éxito.");
    }

    /**
     * Formulario de edición de sede
     */
    public function edit($id)
    {
        $sede = Sede::with('empresa')->findOrFail($id);

        $user = Auth::user();
        if ($user && $user->isSuperAdmin()) {
            $empresas = Empresa::where('activo', true)->orderBy('razon_social', 'asc')->get();
        } elseif ($user) {
            $empresas = $user->empresas()->where('activo', true)->orderBy('razon_social', 'asc')->get();
        } else {
            $empresas = Empresa::where('activo', true)->orderBy('razon_social', 'asc')->get();
        }

        return view('sedes.edit', compact('sede', 'empresas'));
    }

    /**
     * Actualizar información de una sede
     */
    public function update(Request $request, $id)
    {
        $sede = Sede::findOrFail($id);

        $validated = $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'nombre' => 'required|string|max:150',
            'codigo' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:50',
            'activo' => 'nullable|boolean',
        ], [
            'empresa_id.required' => 'Debe seleccionar una empresa.',
            'empresa_id.exists' => 'La empresa seleccionada no es válida.',
            'nombre.required' => 'El nombre de la sede es obligatorio.',
        ]);

        $sede->empresa_id = $validated['empresa_id'];
        $sede->nombre = $validated['nombre'];
        $sede->codigo = $validated['codigo'] ? strtoupper($validated['codigo']) : null;
        $sede->direccion = $validated['direccion'];
        $sede->ciudad = $validated['ciudad'];
        $sede->telefono = $validated['telefono'];
        $sede->activo = $request->has('activo') ? $request->boolean('activo') : false;
        $sede->save();

        if ($request->has('redirect_to_empresa')) {
            return redirect()->route('empresas.show', $sede->empresa_id)
                ->with('success', "Sede '{$sede->nombre}' actualizada exitosamente.");
        }

        return redirect()->route('sedes.index', ['empresa_id' => $sede->empresa_id])
            ->with('success', "Sede '{$sede->nombre}' actualizada correctamente.");
    }

    /**
     * Alternar estado Activo / Inactivo
     */
    public function toggleStatus($id)
    {
        $sede = Sede::findOrFail($id);
        $sede->activo = !$sede->activo;
        $sede->save();

        $estadoStr = $sede->activo ? 'Activada' : 'Desactivada';
        return back()->with('info', "Sede '{$sede->nombre}' ha sido {$estadoStr}.");
    }

    /**
     * Eliminar sede con validación de transacciones contables
     */
    public function destroy($id)
    {
        $sede = Sede::findOrFail($id);

        $comprasCount = ComprobanteCompra::where('sede_id', $sede->id)->count();
        $ventasCount = ComprobanteVenta::where('sede_id', $sede->id)->count();

        if ($comprasCount > 0 || $ventasCount > 0) {
            return back()->with('error', "No es posible eliminar la sede '{$sede->nombre}' porque cuenta con {$comprasCount} compras y {$ventasCount} ventas asociadas. Para no seleccionarla en operaciones, puede Desactivarla.");
        }

        // Si la sede activa en sesión es la eliminada, limpiar sesión
        if (session('active_sede_id') == $sede->id) {
            session()->forget('active_sede_id');
        }

        $sede->users()->detach();
        $sede->delete();

        return back()->with('success', "La sede '{$sede->nombre}' ha sido eliminada exitosamente.");
    }
}
