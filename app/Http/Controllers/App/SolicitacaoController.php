<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\SolicitacaoOrcamento;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SolicitacaoController extends Controller
{
    public function index()
    {
        /** @var Empresa $empresa */
        $empresa = app('empresa.atual');

        return Inertia::render('App/Solicitacoes/Index', [
            'solicitacoes' => SolicitacaoOrcamento::where('empresa_id', $empresa->id)
                ->with(['pacote:id,nome', 'itens:id,solicitacao_id,nome_snapshot,categoria_snapshot'])
                ->latest()
                ->get()
                ->map(fn (SolicitacaoOrcamento $solicitacao) => [
                    'id' => $solicitacao->id,
                    'protocolo' => $solicitacao->protocolo,
                    'nome_contato' => $solicitacao->nome_contato,
                    'email' => $solicitacao->email,
                    'telefone' => $solicitacao->telefone,
                    'data_evento' => $solicitacao->data_evento?->format('d/m/Y'),
                    'convidados' => $solicitacao->convidados,
                    'tipo_local' => $solicitacao->tipo_local,
                    'endereco_local' => $solicitacao->endereco_local,
                    'observacoes' => $solicitacao->observacoes,
                    'aceita_contato' => $solicitacao->aceita_contato,
                    'status' => $solicitacao->status,
                    'retorno_em' => $solicitacao->retorno_em?->format('Y-m-d'),
                    'preco_estimado' => $solicitacao->preco_estimado,
                    'pacote' => $solicitacao->pacote?->nome,
                    'modo_cardapio' => $solicitacao->modo_cardapio,
                    'itens' => $solicitacao->itens->map(fn ($item) => $item->nome_snapshot)->values(),
                    'created_at' => $solicitacao->created_at?->format('d/m/Y H:i'),
                ]),
        ]);
    }

    public function agendarRetorno(Request $request, SolicitacaoOrcamento $solicitacao)
    {
        $this->garantirDaEmpresa($solicitacao);
        $data = $request->validate(['retorno_em' => ['required', 'date']]);
        $solicitacao->update([...$data, 'status' => 'retorno_agendado']);

        return back()->with('success', 'Retorno agendado.');
    }

    public function destroy(SolicitacaoOrcamento $solicitacao)
    {
        $this->garantirDaEmpresa($solicitacao);
        $solicitacao->delete();

        return back()->with('success', 'Solicitação excluída.');
    }

    private function garantirDaEmpresa(SolicitacaoOrcamento $solicitacao): void
    {
        abort_unless((int) $solicitacao->empresa_id === (int) app('empresa.atual')->id, 404);
    }
}
