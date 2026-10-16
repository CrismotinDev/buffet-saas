<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\CategoriaItem;
use App\Models\Empresa;
use App\Models\ItemCatalogo;
use App\Models\Pacote;
use App\Models\SolicitacaoOrcamento;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        /** @var Empresa $empresa */
        $empresa = app('empresa.atual');
        $solicitacoes = SolicitacaoOrcamento::where('empresa_id', $empresa->id)
            ->where(function ($query) {
                $query->whereNull('proposta_token')
                    ->orWhereNull('preco_proposto');
            })
            ->with('pacote:id,nome')
            ->latest()
            ->limit(5)
            ->get();
        $retornos = SolicitacaoOrcamento::where('empresa_id', $empresa->id)
            ->whereNotNull('retorno_em')
            ->where('status', 'retorno_agendado')
            ->with('pacote:id,nome')
            ->orderBy('retorno_em')
            ->limit(6)
            ->get();

        return Inertia::render('App/Dashboard', [
            'metricas' => [
                'solicitacoes_novas' => SolicitacaoOrcamento::where('empresa_id', $empresa->id)
                    ->where(function ($query) {
                        $query->whereNull('proposta_token')
                            ->orWhereNull('preco_proposto');
                    })
                    ->count(),
                'propostas_enviadas' => SolicitacaoOrcamento::where('empresa_id', $empresa->id)->whereNotNull('proposta_token')->whereNotNull('preco_proposto')->count(),
                'aceites_pendentes' => 0,
                'proximos_eventos' => 0,
                'retornos_pendentes' => SolicitacaoOrcamento::where('empresa_id', $empresa->id)->where('status', 'retorno_agendado')->count(),
            ],
            'catalogo' => [
                'itens' => ItemCatalogo::where('empresa_id', $empresa->id)->where('ativo', true)->count(),
                'categorias' => CategoriaItem::where('empresa_id', $empresa->id)->where('ativo', true)->count(),
                'pacotes' => Pacote::where('empresa_id', $empresa->id)->where('ativo', true)->count(),
                'pacotes_publicados' => Pacote::where('empresa_id', $empresa->id)->where('ativo', true)->where('publicado', true)->count(),
            ],
            'solicitacoes_recentes' => $solicitacoes->map(fn (SolicitacaoOrcamento $solicitacao) => [
                'protocolo' => $solicitacao->protocolo,
                'nome' => $solicitacao->nome_contato,
                'pacote' => $solicitacao->pacote?->nome,
                'data_evento' => $solicitacao->data_evento?->format('d/m/Y'),
                'convidados' => $solicitacao->convidados,
                'valor' => $solicitacao->preco_proposto !== null ? max(0, (float) $solicitacao->preco_proposto - (float) $solicitacao->desconto_proposto) : $solicitacao->preco_estimado,
                'status' => $solicitacao->status,
            ]),
            'retornos_pendentes' => $retornos->map(fn (SolicitacaoOrcamento $solicitacao) => [
                'id' => $solicitacao->id,
                'nome' => $solicitacao->nome_contato,
                'email' => $solicitacao->email,
                'telefone' => $solicitacao->telefone,
                'pacote' => $solicitacao->pacote?->nome,
                'retorno_em' => $solicitacao->retorno_em?->format('d/m/Y'),
            ]),
            'empresa' => [
                'id' => $empresa->id,
                'nome_fantasia' => $empresa->nome_fantasia,
                'email' => $empresa->email,
                'timezone' => $empresa->timezone,
                'usuarios_count' => $empresa->usuarios()->wherePivot('ativo', true)->count(),
            ],
            'public_url' => (rtrim((string) config('app.public_url'), '/') ?: rtrim(url(''), '/')).'/e/'.$empresa->slug,
        ]);
    }
}
