<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;

class EmpresaSwitchController extends Controller
{
    public function __invoke(Request $request, int $empresa)
    {
        abort_unless($request->user()->podeAcessarEmpresa(Empresa::findOrFail($empresa)), 403);

        $request->session()->put('empresa_id', $empresa);

        return back()->with('success', 'Empresa ativa alterada.');
    }
}
