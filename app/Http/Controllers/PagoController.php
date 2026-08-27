<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PagoService;
use Exception;

class PagoController extends Controller
{
    protected PagoService $pagoService;

    public function __construct(PagoService $pagoService)
    {
        $this->pagoService = $pagoService;
    }

    /**
     * Registra un nuevo abono/adelanto para una Factura de Proveedor (Compra - CxP)
     */
    public function storeCompra(Request $request)
    {
        $validated = $request->validate([
            'comprobante_compra_id' => 'required|exists:comprobantes_compras,id',
            'fecha_pago' => 'required|date',
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|string',
            'nro_operacion' => 'nullable|string|max:100',
            'banco' => 'nullable|string|max:100',
            'observacion' => 'nullable|string',
        ], [
            'monto.required' => 'Ingrese el monto del pago/adelanto.',
            'monto.min' => 'El monto debe ser mayor a 0.',
        ]);

        try {
            $this->pagoService->registrarAbonoCompra($validated['comprobante_compra_id'], $validated);
            return back()->with('success', '¡Pago / Adelanto registrado correctamente!');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Registra un nuevo abono/adelanto para una Factura de Cliente (Venta - CxC)
     */
    public function storeVenta(Request $request)
    {
        $validated = $request->validate([
            'comprobante_venta_id' => 'required|exists:comprobantes_ventas,id',
            'fecha_pago' => 'required|date',
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|string',
            'nro_operacion' => 'nullable|string|max:100',
            'banco' => 'nullable|string|max:100',
            'observacion' => 'nullable|string',
        ], [
            'monto.required' => 'Ingrese el monto del cobro/adelanto.',
            'monto.min' => 'El monto debe ser mayor a 0.',
        ]);

        try {
            $this->pagoService->registrarAbonoVenta($validated['comprobante_venta_id'], $validated);
            return back()->with('success', '¡Cobro / Adelanto de cliente registrado correctamente!');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Elimina un abono/adelanto y recalcula los saldos
     */
    public function destroy($id)
    {
        try {
            $this->pagoService->eliminarAbono($id);
            return back()->with('success', 'Abono eliminado y saldo recalculado correctamente.');
        } catch (Exception $e) {
            return back()->with('error', 'Error al eliminar el abono: ' . $e->getMessage());
        }
    }
}
