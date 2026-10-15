<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmpresaContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $empresas = $user->empresas()
            ->where('empresas.ativo', true)
            ->wherePivot('ativo', true)
            ->orderBy('nome_fantasia')
            ->get();

        if ($empresas->isEmpty()) {
            abort(403, 'Usuário sem empresa ativa.');
        }

        $empresaId = (int) $request->session()->get('empresa_id');
        $empresa = $empresas->firstWhere('id', $empresaId) ?? $empresas->first();

        $request->session()->put('empresa_id', $empresa->id);
        app()->instance('empresa.atual', $empresa);

        Inertia::share('empresaAtual', [
            'id' => $empresa->id,
            'nome_fantasia' => $empresa->nome_fantasia,
            'slug' => $empresa->slug,
            'papel' => $empresa->pivot->papel,
        ]);

        Inertia::share('empresasDisponiveis', $empresas->map(fn ($item) => [
            'id' => $item->id,
            'nome_fantasia' => $item->nome_fantasia,
            'slug' => $item->slug,
        ])->values());

        return $next($request);
    }
}
