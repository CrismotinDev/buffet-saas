<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class EmpresaController extends Controller
{
    public function show()
    {
        $empresa = app('empresa.atual');
        $this->authorize('view', $empresa);

        return Inertia::render('App/Empresa/Show', [
            'empresa' => $empresa->only([
                'id', 'nome_fantasia', 'razao_social', 'documento', 'slug',
                'email', 'telefone', 'timezone', 'ativo',
            ]),
        ]);
    }

    public function update(Request $request)
    {
        /** @var Empresa $empresa */
        $empresa = app('empresa.atual');
        $this->authorize('update', $empresa);

        $validated = $request->validate([
            'nome_fantasia' => ['required', 'string', 'max:160'],
            'razao_social' => ['nullable', 'string', 'max:180'],
            'documento' => ['nullable', 'string', 'max:32'],
            'email' => ['required', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:32'],
            'timezone' => ['required', 'timezone'],
            'slug' => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('empresas', 'slug')->ignore($empresa->id)],
        ]);

        $empresa->update($validated);

        return back()->with('success', 'Dados da empresa atualizados.');
    }
}
