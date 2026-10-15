<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        /** @var Empresa $empresa */
        $empresa = app('empresa.atual');

        return Inertia::render('App/Dashboard', [
            'metricas' => [
                'solicitacoes_novas' => 0,
                'propostas_enviadas' => 0,
                'aceites_pendentes' => 0,
                'proximos_eventos' => 0,
            ],
            'empresa' => [
                'id' => $empresa->id,
                'nome_fantasia' => $empresa->nome_fantasia,
                'email' => $empresa->email,
                'timezone' => $empresa->timezone,
                'usuarios_count' => $empresa->usuarios()->wherePivot('ativo', true)->count(),
            ],
        ]);
    }
}
