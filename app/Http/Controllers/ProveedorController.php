<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ComprobanteCompra;
use App\Models\Proveedor;
use App\Models\Area;
use App\Models\Sede;
use App\Models\Empresa;
use App\Services\PagoService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProveedorController extends Controller
{
    protected PagoService $pagoService;

    public function __construct(PagoService $pagoService)
    {
        $this->pagoService = $pagoService;
    }

    /**
     * Listado de Cuentas por Pagar (Proveedores)
     */
    public function index(Request $request)
    {
        $empresaId = session('active_empresa_id');
        $sedeId = session('active_sede_id');

        $query = ComprobanteCompra::with(['empresa', 'sede', 'proveedor', 'area', 'pagos']);

        if ($empresaId) {
            $query->where('empresa_id', $empresaId);
        }
        if ($sedeId) {
            $query->where('sede_id', $sedeId);
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            if ($request->estado === 'VENCIDOS') {
                $query->whereIn('estado_pago', ['PENDIENTE', 'CON_ADELANTO'])
                      ->where('fecha_vencimiento', '<', now()->toDateString());
            } else {
                $query->where('estado_pago', $request->estado);
            }
        }

        // Filtro por Área
        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        // Filtro por Moneda
        if ($request->filled('moneda')) {
            $query->where('moneda', $request->moneda);
        }

        // Filtro por Rango de Fechas
        if ($request->filled('fecha_desde')) {
            $query->where('fecha_emision', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_emision', '<=', $request->fecha_hasta);
        }

        $comprobantes = $query->orderBy('fecha_vencimiento', 'asc')->get();

        // Resúmenes
        $totalPendientePen = (clone $query)->where('moneda', 'PEN')->sum('saldo_pendiente');
        $totalPendienteUsd = (clone $query)->where('moneda', 'USD')->sum('saldo_pendiente');
        $totalPagadoPen = (clone $query)->where('moneda', 'PEN')->sum('monto_pagado');

        $areas = Area::where('activo', true)->get();

        return view('proveedores.index', compact(
            'comprobantes',
            'totalPendientePen',
            'totalPendienteUsd',
            'totalPagadoPen',
            'areas'
        ));
    }

    /**
     * Formulario de Registro de Factura de Proveedor
     */
    public function create()
    {
        $empresaId = session('active_empresa_id');
        $sedeId = session('active_sede_id');

        $empresas = Empresa::where('activo', true)->get();
        $sedes = Sede::where('activo', true)->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId))->get();
        $areas = Area::where('activo', true)->get();

        return view('proveedores.create', compact('empresas', 'sedes', 'areas', 'empresaId', 'sedeId'));
    }

    /**
     * Almacena una nueva Factura de Proveedor (CxP)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'sede_id' => 'required|exists:sedes,id',
            'ruc' => 'required|string|size:11',
            'razon_social' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:50',
            'correo' => 'nullable|email|max:150',
            'tipo_comprobante' => 'required|in:FACTURA,BOLETA,RECIBO_HONORARIOS,NOTA_VENTA,OTRO',
            'serie_correlativo' => 'required|string|max:50',
            'area_id' => 'required|exists:areas,id',
            'fecha_emision' => 'required|date',
            'fecha_vencimiento' => 'required|date',
            'moneda' => 'required|in:PEN,USD',
            'monto_total' => 'required|numeric|min:0.01',
            'descripcion' => 'nullable|string',
            // Adelanto inicial opcional
            'tiene_adelanto' => 'nullable|boolean',
            'monto_adelanto' => 'nullable|numeric|min:0',
            'metodo_pago' => 'nullable|string',
            'nro_operacion' => 'nullable|string|max:100',
            'banco' => 'nullable|string|max:100',
        ], [
            'ruc.size' => 'El RUC debe tener exactamente 11 dígitos.',
            'razon_social.required' => 'La Razón Social del proveedor es obligatoria.',
            'serie_correlativo.required' => 'Ingrese el número de serie y correlativo del comprobante.',
            'monto_total.min' => 'El monto total debe ser mayor a 0.',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            // 1. Crear o actualizar Proveedor
            $proveedor = Proveedor::updateOrCreate(
                ['ruc' => $validated['ruc']],
                [
                    'razon_social' => $validated['razon_social'],
                    'direccion' => $validated['direccion'] ?? null,
                    'telefono' => $validated['telefono'] ?? null,
                    'correo' => $validated['correo'] ?? null,
                ]
            );

            $montoTotal = floatval($validated['monto_total']);
            $montoAdelanto = $request->boolean('tiene_adelanto') ? floatval($validated['monto_adelanto'] ?? 0) : 0;

            if ($montoAdelanto > $montoTotal) {
                return back()->withErrors(['monto_adelanto' => 'El adelanto no puede ser mayor al monto total.'])->withInput();
            }

            $saldo = max(0, $montoTotal - $montoAdelanto);
            $estado = 'PENDIENTE';
            if ($saldo <= 0.001) {
                $estado = 'PAGADO';
            } elseif ($montoAdelanto > 0) {
                $estado = 'CON_ADELANTO';
            }

            // 2. Crear Comprobante de Compra
            $compra = ComprobanteCompra::create([
                'empresa_id' => $validated['empresa_id'],
                'sede_id' => $validated['sede_id'],
                'proveedor_id' => $proveedor->id,
                'area_id' => $validated['area_id'],
                'tipo_comprobante' => $validated['tipo_comprobante'],
                'serie_correlativo' => strtoupper($validated['serie_correlativo']),
                'fecha_emision' => $validated['fecha_emision'],
                'fecha_vencimiento' => $validated['fecha_vencimiento'],
                'moneda' => $validated['moneda'],
                'monto_total' => $montoTotal,
                'monto_pagado' => $montoAdelanto,
                'saldo_pendiente' => $saldo,
                'estado_pago' => $estado,
                'descripcion' => $validated['descripcion'] ?? null,
                'user_id' => Auth::id() ?? 1,
            ]);

            // 3. Registrar primer adelanto si se indicó
            if ($montoAdelanto > 0) {
                $this->pagoService->registrarAbonoCompra($compra->id, [
                    'fecha_pago' => $validated['fecha_emision'],
                    'monto' => $montoAdelanto,
                    'metodo_pago' => $validated['metodo_pago'] ?? 'TRANSFERENCIA',
                    'nro_operacion' => $validated['nro_operacion'] ?? null,
                    'banco' => $validated['banco'] ?? null,
                    'observacion' => 'Adelanto registrado al ingresar la factura',
                ]);
            }

            return redirect()->route('proveedores.index')->with('success', '¡Factura de proveedor registrada exitosamente!');
        });
    }

    /**
     * Obtiene el detalle de una compra y su historial de pagos en JSON (para modal)
     */
    public function show($id)
    {
        $compra = ComprobanteCompra::with(['proveedor', 'empresa', 'sede', 'area', 'pagos.user'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'compra' => $compra,
            'pagos' => $compra->pagos,
            'semaforo_color' => $compra->semaforo_color,
            'semaforo_texto' => $compra->semaforo_texto,
        ]);
    }

    /**
     * Elimina un comprobante
     */
    public function destroy($id)
    {
        $compra = ComprobanteCompra::findOrFail($id);
        $compra->delete();

        return redirect()->route('proveedores.index')->with('success', 'Comprobante eliminado correctamente.');
    }
}
