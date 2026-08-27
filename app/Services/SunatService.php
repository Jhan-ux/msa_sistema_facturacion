<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SunatService
{
    protected ?string $token;
    protected string $rucApiUrl;
    protected string $dniApiUrl;

    public function __construct()
    {
        $this->token = env('SUNAT_API_TOKEN', null);
        $this->rucApiUrl = env('SUNAT_API_URL', 'https://api.apis.net.pe/v2/sunat/ruc');
        $this->dniApiUrl = env('RENIEC_API_URL', 'https://api.apis.net.pe/v2/reniec/dni');
    }

    /**
     * Consulta de datos oficiales de RUC (11 dígitos)
     */
    public function consultarRuc(string $ruc): array
    {
        $ruc = trim($ruc);
        if (strlen($ruc) !== 11 || !ctype_digit($ruc)) {
            return [
                'success' => false,
                'message' => 'El RUC debe tener exactamente 11 dígitos numéricos.'
            ];
        }

        try {
            $headers = ['Referer' => config('app.url')];
            if (!empty($this->token)) {
                $headers['Authorization'] = 'Bearer ' . $this->token;
            }

            $response = Http::withHeaders($headers)
                ->timeout(6)
                ->get($this->rucApiUrl, ['numero' => $ruc]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'data' => [
                        'numero' => $data['numero'] ?? $ruc,
                        'razon_social' => $data['razonSocial'] ?? ($data['nombre'] ?? ''),
                        'estado' => $data['estado'] ?? 'ACTIVO',
                        'condicion' => $data['condicion'] ?? 'HABIDO',
                        'direccion' => $data['direccion'] ?? ($data['direccionCompleta'] ?? ''),
                        'departamento' => $data['departamento'] ?? '',
                        'provincia' => $data['provincia'] ?? '',
                        'distrito' => $data['distrito'] ?? '',
                        'ubigeo' => $data['ubigeo'] ?? '',
                    ]
                ];
            }

            // Fallback local: Buscar en proveedores, clientes o empresas registradas
            $local = \App\Models\Proveedor::where('ruc', $ruc)->first() 
                  ?? \App\Models\Empresa::where('ruc', $ruc)->first()
                  ?? \App\Models\Cliente::where('numero_documento', $ruc)->first();

            if ($local) {
                return [
                    'success' => true,
                    'data' => [
                        'numero' => $ruc,
                        'razon_social' => $local->razon_social ?? $local->razon_social_nombre,
                        'estado' => $local->estado_sunat ?? 'ACTIVO',
                        'condicion' => $local->condicion_sunat ?? 'HABIDO',
                        'direccion' => $local->direccion ?? '',
                    ]
                ];
            }

            return [
                'success' => false,
                'message' => 'No se encontró información para el RUC en SUNAT ni en la base local.'
            ];
        } catch (\Exception $e) {
            Log::warning("Error consultando API SUNAT: " . $e->getMessage());

            // Fallback en caso de error de red
            $local = \App\Models\Proveedor::where('ruc', $ruc)->first() 
                  ?? \App\Models\Empresa::where('ruc', $ruc)->first()
                  ?? \App\Models\Cliente::where('numero_documento', $ruc)->first();

            if ($local) {
                return [
                    'success' => true,
                    'data' => [
                        'numero' => $ruc,
                        'razon_social' => $local->razon_social ?? $local->razon_social_nombre,
                        'estado' => $local->estado_sunat ?? 'ACTIVO',
                        'condicion' => $local->condicion_sunat ?? 'HABIDO',
                        'direccion' => $local->direccion ?? '',
                    ]
                ];
            }

            return [
                'success' => false,
                'message' => 'Servicio de SUNAT no disponible. Ingrese los datos manualmente.'
            ];
        }
    }

    /**
     * Consulta de datos oficiales de DNI (8 dígitos)
     */
    public function consultarDni(string $dni): array
    {
        $dni = trim($dni);
        if (strlen($dni) !== 8 || !ctype_digit($dni)) {
            return [
                'success' => false,
                'message' => 'El DNI debe tener exactamente 8 dígitos numéricos.'
            ];
        }

        try {
            $headers = ['Referer' => config('app.url')];
            if (!empty($this->token)) {
                $headers['Authorization'] = 'Bearer ' . $this->token;
            }

            $response = Http::withHeaders($headers)
                ->timeout(6)
                ->get($this->dniApiUrl, ['numero' => $dni]);

            if ($response->successful()) {
                $data = $response->json();
                $nombreCompleto = trim(($data['nombres'] ?? '') . ' ' . ($data['apellidoPaterno'] ?? '') . ' ' . ($data['apellidoMaterno'] ?? ''));
                if (empty($nombreCompleto) && isset($data['nombre'])) {
                    $nombreCompleto = $data['nombre'];
                }

                return [
                    'success' => true,
                    'data' => [
                        'numero' => $data['numero'] ?? $dni,
                        'nombres' => $data['nombres'] ?? '',
                        'apellido_paterno' => $data['apellidoPaterno'] ?? '',
                        'apellido_materno' => $data['apellidoMaterno'] ?? '',
                        'nombre_completo' => $nombreCompleto,
                        'direccion' => $data['direccion'] ?? '',
                    ]
                ];
            }

            // Fallback local
            $local = \App\Models\Cliente::where('numero_documento', $dni)->first();
            if ($local) {
                return [
                    'success' => true,
                    'data' => [
                        'numero' => $dni,
                        'nombre_completo' => $local->razon_social_nombre,
                        'direccion' => $local->direccion ?? '',
                    ]
                ];
            }

            return [
                'success' => false,
                'message' => 'No se encontró información para el DNI ingresado.'
            ];
        } catch (\Exception $e) {
            Log::warning("Error consultando API RENIEC: " . $e->getMessage());

            $local = \App\Models\Cliente::where('numero_documento', $dni)->first();
            if ($local) {
                return [
                    'success' => true,
                    'data' => [
                        'numero' => $dni,
                        'nombre_completo' => $local->razon_social_nombre,
                        'direccion' => $local->direccion ?? '',
                    ]
                ];
            }

            return [
                'success' => false,
                'message' => 'Servicio de RENIEC no disponible. Ingrese los datos manualmente.'
            ];
        }
    }
}
