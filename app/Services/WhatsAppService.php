<?php

namespace App\Services;

use App\Models\ComprobanteVenta;

class WhatsAppService
{
    /**
     * Genera el enlace de cobro de WhatsApp con mensaje personalizado
     */
    public function generarEnlaceCobro(ComprobanteVenta $venta): string
    {
        $cliente = $venta->cliente;
        $empresa = $venta->empresa;
        $sede = $venta->sede;

        $telefono = preg_replace('/[^0-9]/', '', $cliente->telefono ?? '');
        if (empty($telefono)) {
            return '';
        }

        // Si tiene 9 dígitos (celular Perú sin código), anteponer 51
        if (strlen($telefono) === 9) {
            $telefono = '51' . $telefono;
        }

        $nombreEmpresa = $empresa->nombre_comercial ?? $empresa->razon_social;
        $nombreCliente = $cliente->razon_social_nombre;
        $comprobante = $venta->tipo_comprobante . ' ' . $venta->serie_correlativo;
        $simbolo = $venta->moneda === 'USD' ? '$' : 'S/';
        $montoTotal = $simbolo . ' ' . number_format($venta->monto_total, 2);
        $montoCobrado = $simbolo . ' ' . number_format($venta->monto_cobrado, 2);
        $saldoPendiente = $simbolo . ' ' . number_format($venta->saldo_pendiente, 2);
        $fechaVenc = $venta->fecha_vencimiento->format('d/m/Y');
        $concepto = $venta->descripcion ?? 'Servicio / Repuestos';
        $cuentas = $empresa->cuentas_bancarias ?? '';

        $mensaje = "Hola estimado(a) *{$nombreCliente}*, le saluda el área de Contabilidad de *{$nombreEmpresa}* ({$sede->nombre}).\n\n";
        $mensaje .= "Le escribimos para recordarle el estado de su comprobante *{$comprobante}* por concepto de: _{$concepto}_.\n\n";
        $mensaje .= "📊 *Resumen de Cuenta:*\n";
        $mensaje .= "• Importe Total: {$montoTotal}\n";
        if ($venta->monto_cobrado > 0) {
            $mensaje .= "• Adelanto registrado: {$montoCobrado}\n";
        }
        $mensaje .= "• *Saldo Pendiente por Cancelar:* *{$saldoPendiente}*\n";
        $mensaje .= "• Fecha de Vencimiento: *{$fechaVenc}*\n\n";

        if (!empty($cuentas)) {
            $mensaje .= "💳 *Cuentas Bancarias Autorizadas:*\n{$cuentas}\n\n";
        }

        $mensaje .= "Agradecemos coordinar la cancelación del saldo pendiente y enviarnos el comprobante de pago por este medio. ¡Muchas gracias!";

        return 'https://api.whatsapp.com/send?phone=' . $telefono . '&text=' . urlencode($mensaje);
    }
}
