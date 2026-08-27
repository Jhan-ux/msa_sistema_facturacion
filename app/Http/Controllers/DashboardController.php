<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(Request $request)
    {
        $empresaId = session('active_empresa_id');
        $sedeId = session('active_sede_id');

        $metricas = $this->dashboardService->getMetricas($empresaId, $sedeId);

        return view('dashboard.index', compact('metricas'));
    }
}
