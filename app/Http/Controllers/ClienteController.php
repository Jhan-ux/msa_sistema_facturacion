<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ComprobanteVenta;
use App\Models\Cliente;
use App\Models\Area;
use App\Models\Sede;
use App\Models\Empresa;
use App\Services\PagoService;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    protected PagoService $pagoService;
    protected WhatsAppService $whatsAppService;

    public function __construct(PagoService $pagoService, WhatsAppService $whatsAppService)
    {
        $this->pagoService = $pagoService;
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Listado de Cuentas por Cobrar (Clientes)
     */
    public function index(Request $request)
    {
        $empresaId = session('active_empresa_id');
        $sedeId = session('active_sede_id');

        $query = ComprobanteVenta::with(['empresa', 'sede', 'cliente', 'area', 'pagos']);

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

        // Enlaces de WhatsApp generados
        foreach ($comprobantes as $comp) {
            $comp->whatsapp_link = $this->whatsAppService->generarEnlaceCobro($comp);
        }

        // Resúmenes
        $totalPendientePen = (clone $query)->where('moneda', 'PEN')->sum('saldo_pendiente');
        $totalPendienteUsd = (clone $query)->where('moneda', 'USD')->sum('saldo_pendiente');
        $totalCobradoPen = (clone $query)->where('moneda', 'PEN')->sum('monto_cobrado');

        $areas = Area::where('activo', true)->get();

        return view('clientes.index', compact(
            'comprobantes',
            'totalPendientePen',
            'totalPendienteUsd',
            'totalCobradoPen',
            'areas'
        ));
    }

    /**
     * Formulario de Registro de Factura / Venta a Cliente
     */
    public function create()
    {
        $empresaId = session('active_empresa_id');
        $sedeId = session('active_sede_id');

        $empresas = Empresa::where('activo', true)->get();
        $sedes = Sede::where('activo', true)->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId))->get();
        $areas = Area::where('activo', true)->get();

        return view('clientes.create', compact('empresas', 'sedes', 'areas', 'empresaId', 'sedeId'));
    }

    /**
     * Almacena una nueva Factura / Venta a Cliente (CxC)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'sede_id' => 'required|exists:sedes,id',
            'tipo_documento' => 'required|in:RUC,DNI,CE,PASAPORTE',
            'numero_documento' => 'required|string|max:20',
            'razon_social_nombre' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:50',
            'correo' => 'nullable|email|max:150',
            'tipo_comprobante' => 'required|in:FACTURA,BOLETA,COTIZACION,NOTA_VENTA,OTRO',
            'serie_correlativo' => 'required|string|max:50',
            'area_id' => 'required|exists:areas,id',
            'fecha_emision' => 'required|date',
            'fecha_vencimiento' => 'required|date',
            'moneda' => 'required|in:PEN,USD',
            'monto_total' => 'required|numeric|min:0.01',
            'descripcion' => 'nullable|string',
            // Adelanto inicial del cliente
            'tiene_adelanto' => 'nullable|boolean',
            'monto_adelanto' => 'nullable|numeric|min:0',
            'metodo_pago' => 'nullable|string',
            'nro_operacion' => 'nullable|string|max:100',
            'banco' => 'nullable|string|max:100',
        ], [
            'numero_documento.required' => 'Ingrese el número de RUC o DNI.',
            'razon_social_nombre.required' => 'El nombre o Razón Social del cliente es obligatorio.',
            'serie_correlativo.required' => 'Ingrese el número de serie y correlativo del comprobante.',
            'monto_total.min' => 'El monto total debe ser mayor a 0.',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            // 1. Crear o actualizar Cliente
            $cliente = Cliente::updateOrCreate(
                ['numero_documento' => $validated['numero_documento']],
                [
                    'tipo_documento' => $validated['tipo_documento'],
                    'razon_social_nombre' => $validated['razon_social_nombre'],
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

            // 2. Crear Comprobante de Venta
            $venta = ComprobanteVenta::create([
                'empresa_id' => $validated['empresa_id'],
                'sede_id' => $validated['sede_id'],
                'cliente_id' => $cliente->id,
                'area_id' => $validated['area_id'],
                'tipo_comprobante' => $validated['tipo_comprobante'],
                'serie_correlativo' => strtoupper($validated['serie_correlativo']),
                'fecha_emision' => $validated['fecha_emision'],
                'fecha_vencimiento' => $validated['fecha_vencimiento'],
                'moneda' => $validated['moneda'],
                'monto_total' => $montoTotal,
                'monto_cobrado' => $montoAdelanto,
                'saldo_pendiente' => $saldo,
                'estado_pago' => $estado,
                'descripcion' => $validated['descripcion'] ?? null,
                'user_id' => Auth::id() ?? 1,
            ]);

            // 3. Registrar primer adelanto si se indicó
            if ($montoAdelanto > 0) {
                $this->pagoService->registrarAbonoVenta($venta->id, [
                    'fecha_pago' => $validated['fecha_emision'],
                    'monto' => $montoAdelanto,
                    'metodo_pago' => $validated['metodo_pago'] ?? 'TRANSFERENCIA',
                    'nro_operacion' => $validated['nro_operacion'] ?? null,
                    'banco' => $validated['banco'] ?? null,
                    'observacion' => 'Adelanto recibido al emitir el comprobante',
                ]);
            }

            return redirect()->route('clientes.index')->with('success', '¡Comprobante de cliente registrado exitosamente!');
        });
    }

    /**
     * Obtiene el detalle de una venta y su historial de cobros en JSON (para modal)
     */
    public function show($id)
    {
        $venta = ComprobanteVenta::with(['cliente', 'empresa', 'sede', 'area', 'pagos.user'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'venta' => $venta,
            'pagos' => $venta->pagos,
            'semaforo_color' => $venta->semaforo_color,
            'semaforo_texto' => $venta->semaforo_texto,
            'whatsapp_link' => $this->whatsAppService->generarEnlaceCobro($venta),
        ]);
    }

    /**
     * Elimina un comprobante de venta
     */
    public function destroy($id)
    {
        $venta = ComprobanteVenta::findOrFail($id);
        $venta->delete();

        return redirect()->route('clientes.index')->with('success', 'Comprobante eliminado correctamente.');
    }
}
