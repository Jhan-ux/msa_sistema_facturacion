<?php

namespace App\Services;

use App\Models\ComprobanteCompra;
use App\Models\ComprobanteVenta;
use App\Models\PagoAbono;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class PagoService
{
    /**
     * Registra un nuevo abono/adelanto a una factura de proveedor (Compra - CxP)
     */
    public function registrarAbonoCompra(int $compraId, array $datos, ?int $userId = null): PagoAbono
    {
        return DB::transaction(function () use ($compraId, $datos, $userId) {
            $compra = ComprobanteCompra::lockForUpdate()->findOrFail($compraId);

            $montoAbono = round(floatval($datos['monto']), 2);
            if ($montoAbono <= 0) {
                throw new Exception("El monto del adelanto debe ser mayor a cero.");
            }

            if ($montoAbono > round(floatval($compra->saldo_pendiente), 2)) {
                throw new Exception("El monto no puede ser mayor al saldo pendiente (" . number_format($compra->saldo_pendiente, 2) . ").");
            }

            $pago = PagoAbono::create([
                'tipo_operacion' => 'COMPRA_PAGO',
                'comprobante_compra_id' => $compra->id,
                'fecha_pago' => $datos['fecha_pago'],
                'monto' => $montoAbono,
                'metodo_pago' => $datos['metodo_pago'] ?? 'TRANSFERENCIA',
                'nro_operacion' => $datos['nro_operacion'] ?? null,
                'banco' => $datos['banco'] ?? null,
                'comprobante_voucher' => $datos['comprobante_voucher'] ?? null,
                'observacion' => $datos['observacion'] ?? null,
                'user_id' => $userId ?? Auth::id() ?? 1,
            ]);

            // Recalcular montos del comprobante con redondeo estricto
            $totalPagado = round(floatval(PagoAbono::where('comprobante_compra_id', $compra->id)->sum('monto')), 2);
            $saldo = max(0, round(floatval($compra->monto_total) - $totalPagado, 2));

            $estado = 'PENDIENTE';
            if ($saldo <= 0.00) {
                $estado = 'PAGADO';
            } elseif ($totalPagado > 0) {
                $estado = 'CON_ADELANTO';
            }

            $compra->update([
                'monto_pagado' => $totalPagado,
                'saldo_pendiente' => $saldo,
                'estado_pago' => $estado,
            ]);

            return $pago;
        });
    }

    /**
     * Registra un nuevo abono/adelanto a una factura de cliente (Venta - CxC)
     */
    public function registrarAbonoVenta(int $ventaId, array $datos, ?int $userId = null): PagoAbono
    {
        return DB::transaction(function () use ($ventaId, $datos, $userId) {
            $venta = ComprobanteVenta::lockForUpdate()->findOrFail($ventaId);

            $montoAbono = round(floatval($datos['monto']), 2);
            if ($montoAbono <= 0) {
                throw new Exception("El monto del adelanto debe ser mayor a cero.");
            }

            if ($montoAbono > round(floatval($venta->saldo_pendiente), 2)) {
                throw new Exception("El monto no puede ser mayor al saldo pendiente (" . number_format($venta->saldo_pendiente, 2) . ").");
            }

            $pago = PagoAbono::create([
                'tipo_operacion' => 'VENTA_COBRO',
                'comprobante_venta_id' => $venta->id,
                'fecha_pago' => $datos['fecha_pago'],
                'monto' => $montoAbono,
                'metodo_pago' => $datos['metodo_pago'] ?? 'TRANSFERENCIA',
                'nro_operacion' => $datos['nro_operacion'] ?? null,
                'banco' => $datos['banco'] ?? null,
                'comprobante_voucher' => $datos['comprobante_voucher'] ?? null,
                'observacion' => $datos['observacion'] ?? null,
                'user_id' => $userId ?? Auth::id() ?? 1,
            ]);

            // Recalcular montos con redondeo estricto
            $totalCobrado = round(floatval(PagoAbono::where('comprobante_venta_id', $venta->id)->sum('monto')), 2);
            $saldo = max(0, round(floatval($venta->monto_total) - $totalCobrado, 2));

            $estado = 'PENDIENTE';
            if ($saldo <= 0.00) {
                $estado = 'PAGADO';
            } elseif ($totalCobrado > 0) {
                $estado = 'CON_ADELANTO';
            }

            $venta->update([
                'monto_cobrado' => $totalCobrado,
                'saldo_pendiente' => $saldo,
                'estado_pago' => $estado,
            ]);

            return $pago;
        });
    }

    /**
     * Elimina un abono y recalcula el saldo del comprobante asociado
     */
    public function eliminarAbono(int $pagoId): bool
    {
        return DB::transaction(function () use ($pagoId) {
            $pago = PagoAbono::findOrFail($pagoId);

            if ($pago->comprobante_compra_id) {
                $compraId = $pago->comprobante_compra_id;
                $pago->delete();

                $compra = ComprobanteCompra::lockForUpdate()->findOrFail($compraId);
                $totalPagado = round(floatval(PagoAbono::where('comprobante_compra_id', $compraId)->sum('monto')), 2);
                $saldo = max(0, round(floatval($compra->monto_total) - $totalPagado, 2));

                $estado = 'PENDIENTE';
                if ($saldo <= 0.00) {
                    $estado = 'PAGADO';
                } elseif ($totalPagado > 0) {
                    $estado = 'CON_ADELANTO';
                }

                $compra->update([
                    'monto_pagado' => $totalPagado,
                    'saldo_pendiente' => $saldo,
                    'estado_pago' => $estado,
                ]);
            } elseif ($pago->comprobante_venta_id) {
                $ventaId = $pago->comprobante_venta_id;
                $pago->delete();

                $venta = ComprobanteVenta::lockForUpdate()->findOrFail($ventaId);
                $totalCobrado = round(floatval(PagoAbono::where('comprobante_venta_id', $ventaId)->sum('monto')), 2);
                $saldo = max(0, round(floatval($venta->monto_total) - $totalCobrado, 2));

                $estado = 'PENDIENTE';
                if ($saldo <= 0.00) {
                    $estado = 'PAGADO';
                } elseif ($totalCobrado > 0) {
                    $estado = 'CON_ADELANTO';
                }

                $venta->update([
                    'monto_cobrado' => $totalCobrado,
                    'saldo_pendiente' => $saldo,
                    'estado_pago' => $estado,
                ]);
            }

            return true;
        });
    }
}
