<?php

namespace App\Http\Controllers;

use App\Services\SunatService;
use Illuminate\Http\JsonResponse;

class SunatApiController extends Controller
{
    protected SunatService $sunatService;

    public function __construct(SunatService $sunatService)
    {
        $this->sunatService = $sunatService;
    }

    /**
     * Endpoint API para consultar RUC (11 dígitos)
     */
    public function consultarRuc(string $ruc): JsonResponse
    {
        $resultado = $this->sunatService->consultarRuc($ruc);
        return response()->json($resultado);
    }

    /**
     * Endpoint API para consultar DNI (8 dígitos)
     */
    public function consultarDni(string $dni): JsonResponse
    {
        $resultado = $this->sunatService->consultarDni($dni);
        return response()->json($resultado);
    }
}
