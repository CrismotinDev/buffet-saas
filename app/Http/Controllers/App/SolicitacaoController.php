<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\ItemCatalogo;
use App\Models\SolicitacaoItem;
use App\Models\SolicitacaoOrcamento;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SolicitacaoController extends Controller
{
    public function index()
    {
        /** @var Empresa $empresa */
        $empresa = app('empresa.atual');

        return Inertia::render('App/Solicitacoes/Index', [
            'solicitacoes' => SolicitacaoOrcamento::where('empresa_id', $empresa->id)
                ->with(['pacote:id,nome', 'itens:id,solicitacao_id,nome_snapshot,categoria_snapshot,quantidade,preco_unitario_snapshot,subtotal_snapshot'])
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
                    'preco_proposto' => $solicitacao->preco_proposto,
                    'desconto_proposto' => $solicitacao->desconto_proposto,
                    'validade_proposta' => $solicitacao->validade_proposta?->format('Y-m-d'),
                    'observacoes_proposta' => $solicitacao->observacoes_proposta,
                    'proposta_token' => $solicitacao->proposta_token,
                    'proposta_url' => $solicitacao->proposta_token ? $this->urlDaProposta($solicitacao->proposta_token) : null,
                    'pacote' => $solicitacao->pacote?->nome,
                    'modo_cardapio' => $solicitacao->modo_cardapio,
                    'valor_cardapio_pessoa' => data_get($solicitacao->calculo_snapshot, 'preco_personalizado_por_pessoa', 0),
                    'itens' => $solicitacao->itens->map(fn ($item) => ['id' => $item->id, 'item_id' => $item->item_id, 'nome' => $item->nome_snapshot, 'quantidade' => $item->quantidade, 'preco_unitario' => $item->preco_unitario_snapshot, 'subtotal' => $item->subtotal_snapshot])->values(),
                    'created_at' => $solicitacao->created_at?->format('d/m/Y H:i'),
                ]),
            'itens_disponiveis' => ItemCatalogo::where('empresa_id', $empresa->id)
                ->where('ativo', true)
                ->with('categorias:id,nome')
                ->orderBy('tipo_item')
                ->orderBy('nome')
                ->get(['id', 'nome', 'tipo_item', 'unidade', 'preco_unitario'])
                ->map(fn (ItemCatalogo $item) => [
                    'id' => $item->id,
                    'nome' => $item->nome,
                    'tipo_item' => $item->tipo_item,
                    'unidade' => $item->unidade,
                    'preco_unitario' => $item->preco_unitario,
                    'categoria_id' => $item->categorias->first()?->id,
                    'categoria' => $item->categorias->first()?->nome,
                ])->values(),
        ]);
    }

    public function agendarRetorno(Request $request, SolicitacaoOrcamento $solicitacao)
    {
        $this->garantirDaEmpresa($solicitacao);
        $data = $request->validate(['retorno_em' => ['required', 'date']]);
        $solicitacao->update([...$data, 'status' => $solicitacao->proposta_enviada_em ? 'proposta_enviada' : 'retorno_agendado']);

        return back()->with('success', 'Retorno agendado.');
    }

    public function atualizarQuantidadeItem(Request $request, SolicitacaoOrcamento $solicitacao, SolicitacaoItem $item)
    {
        $this->garantirDaEmpresa($solicitacao);
        abort_unless((int) $item->solicitacao_id === (int) $solicitacao->id, 404);
        abort_if((float) $item->preco_unitario_snapshot <= 0, 422, 'Somente bebidas podem ter a quantidade alterada.');

        $data = $request->validate(['quantidade' => ['required', 'integer', 'min:1', 'max:9999']]);
        $item->update([
            'quantidade' => $data['quantidade'],
            'subtotal_snapshot' => round($data['quantidade'] * (float) $item->preco_unitario_snapshot, 2),
        ]);

        $itens = $solicitacao->itens()->get();
        $snapshot = $solicitacao->calculo_snapshot ?: [];
        $snapshot['itens'] = $itens->map(fn ($registro) => [
            'id' => $registro->item_id,
            'nome' => $registro->nome_snapshot,
            'quantidade' => $registro->quantidade,
            'preco_unitario' => $registro->preco_unitario_snapshot,
            'subtotal' => $registro->subtotal_snapshot,
        ])->values()->all();
        $bebidasTotal = $itens->sum(fn ($registro) => (float) $registro->subtotal_snapshot);
        $base = (float) ($snapshot['base'] ?? 0);
        $snapshot['bebidas_total'] = round($bebidasTotal, 2);
        $snapshot['total'] = round($base + $bebidasTotal, 2);
        $alteracoes = ['preco_estimado' => $snapshot['total'], 'calculo_snapshot' => $snapshot];
        if ($solicitacao->preco_proposto !== null) {
            $alteracoes['preco_proposto'] = $snapshot['total'];
        }
        $solicitacao->update($alteracoes);

        return back()->with('success', 'Quantidade da bebida atualizada.');
    }

    public function adicionarItem(Request $request, SolicitacaoOrcamento $solicitacao)
    {
        $this->garantirDaEmpresa($solicitacao);
        $data = $request->validate([
            'item_id' => ['required', 'integer'],
            'quantidade' => ['required', 'integer', 'min:1', 'max:9999'],
        ]);
        $item = ItemCatalogo::where('empresa_id', app('empresa.atual')->id)
            ->where('ativo', true)
            ->with('categorias:id,nome')
            ->findOrFail($data['item_id']);
        $categoria = $item->categorias->first();
        $registro = $solicitacao->itens()->where('item_id', $item->id)->first();
        if ($registro) {
            $registro->update([
                'quantidade' => $data['quantidade'],
                'preco_unitario_snapshot' => $item->preco_unitario ?? 0,
                'subtotal_snapshot' => round($data['quantidade'] * (float) ($item->preco_unitario ?? 0), 2),
            ]);
        } else {
            $solicitacao->itens()->create([
                'item_id' => $item->id,
                'categoria_id' => $categoria?->id ?? 1,
                'nome_snapshot' => $item->nome,
                'categoria_snapshot' => $categoria?->nome ?? 'Adicionado pela equipe',
                'quantidade' => $data['quantidade'],
                'preco_unitario_snapshot' => $item->preco_unitario ?? 0,
                'subtotal_snapshot' => round($data['quantidade'] * (float) ($item->preco_unitario ?? 0), 2),
            ]);
        }

        $this->recalcularSolicitacao($solicitacao);

        return back()->with('success', 'Item adicionado à solicitação.');
    }

    public function atualizarValorCardapio(Request $request, SolicitacaoOrcamento $solicitacao)
    {
        $this->garantirDaEmpresa($solicitacao);
        abort_unless($solicitacao->modo_cardapio === 'personalizado', 422);
        $data = $request->validate(['valor_cardapio_pessoa' => ['required', 'numeric', 'min:0']]);
        $snapshot = $solicitacao->calculo_snapshot ?: [];
        $valorPessoa = (float) $data['valor_cardapio_pessoa'];
        $base = $valorPessoa * (int) $solicitacao->convidados;
        $bebidasTotal = collect($snapshot['itens'] ?? [])->sum(fn ($item) => (float) ($item['subtotal'] ?? 0));
        $snapshot['preco_personalizado_por_pessoa'] = $valorPessoa;
        $snapshot['base'] = round($base, 2);
        $snapshot['bebidas_total'] = round($bebidasTotal, 2);
        $snapshot['total'] = round($base + $bebidasTotal, 2);
        $alteracoes = ['preco_estimado' => $snapshot['total'], 'calculo_snapshot' => $snapshot];
        if ($solicitacao->preco_proposto !== null) {
            $alteracoes['preco_proposto'] = $snapshot['total'];
        }
        $solicitacao->update($alteracoes);

        return back()->with('success', 'Valor do cardápio atualizado.');
    }

    public function destroy(SolicitacaoOrcamento $solicitacao)
    {
        $this->garantirDaEmpresa($solicitacao);
        $solicitacao->delete();

        return back()->with('success', 'Solicitação excluída.');
    }

    public function configurarProposta(Request $request, SolicitacaoOrcamento $solicitacao)
    {
        $this->garantirDaEmpresa($solicitacao);
        $data = $request->validate([
            'preco_proposto' => ['required', 'numeric', 'min:0'],
            'desconto_proposto' => ['nullable', 'numeric', 'min:0'],
            'validade_proposta' => ['nullable', 'date'],
            'observacoes_proposta' => ['nullable', 'string', 'max:4000'],
        ]);
        $data['desconto_proposto'] = $data['desconto_proposto'] ?? 0;
        $data['proposta_token'] = $solicitacao->proposta_token ?: (string) Str::uuid();
        $solicitacao->update([...$data, 'proposta_enviada_em' => now(), 'status' => 'proposta_enviada']);

        return back()->with('success', 'Proposta formalizada.');
    }

    public function marcarPropostaEnviada(SolicitacaoOrcamento $solicitacao)
    {
        $this->garantirDaEmpresa($solicitacao);
        abort_unless($solicitacao->proposta_token && $solicitacao->preco_proposto !== null, 422);

        $solicitacao->update([
            'status' => 'proposta_enviada',
            'proposta_enviada_em' => now(),
        ]);

        return back()->with('success', 'Proposta marcada como enviada.');
    }

    public function proposta(string $token)
    {
        $solicitacao = SolicitacaoOrcamento::where('proposta_token', $token)
            ->with(['pacote.empresa', 'itens'])
            ->firstOrFail();

        return Inertia::render('Publico/Proposta', [
            'proposta' => [
                'empresa' => $solicitacao->pacote->empresa->only(['nome_fantasia', 'telefone', 'email', 'logo_path']),
                'cliente' => $solicitacao->nome_contato,
                'email' => $solicitacao->email,
                'telefone' => $solicitacao->telefone,
                'protocolo' => $solicitacao->protocolo,
                'pacote' => $solicitacao->pacote->nome,
                'data_evento' => $solicitacao->data_evento?->format('d/m/Y'),
                'convidados' => $solicitacao->convidados,
                'local' => $solicitacao->tipo_local === 'externo' ? $solicitacao->endereco_local : 'No estabelecimento',
                'itens' => $solicitacao->itens->map(fn ($item) => ['nome' => $item->nome_snapshot, 'quantidade' => $item->quantidade, 'preco_unitario' => $item->preco_unitario_snapshot, 'subtotal' => $item->subtotal_snapshot])->values(),
                'preco_estimado' => $solicitacao->preco_estimado,
                'preco_proposto' => $solicitacao->preco_proposto,
                'desconto' => $solicitacao->desconto_proposto,
                'validade' => $solicitacao->validade_proposta?->format('d/m/Y'),
                'observacoes' => $solicitacao->observacoes_proposta,
            ],
        ]);
    }

    private function garantirDaEmpresa(SolicitacaoOrcamento $solicitacao): void
    {
        abort_unless((int) $solicitacao->empresa_id === (int) app('empresa.atual')->id, 404);
    }

    private function recalcularSolicitacao(SolicitacaoOrcamento $solicitacao): void
    {
        $itens = $solicitacao->itens()->get();
        $snapshot = $solicitacao->calculo_snapshot ?: [];
        $snapshot['itens'] = $itens->map(fn ($registro) => [
            'id' => $registro->item_id,
            'nome' => $registro->nome_snapshot,
            'quantidade' => $registro->quantidade,
            'preco_unitario' => $registro->preco_unitario_snapshot,
            'subtotal' => $registro->subtotal_snapshot,
        ])->values()->all();
        $itensTotal = $itens->sum(fn ($registro) => (float) $registro->subtotal_snapshot);
        $base = (float) ($snapshot['base'] ?? 0);
        $snapshot['bebidas_total'] = $itens->where('preco_unitario_snapshot', '>', 0)->sum(fn ($registro) => (float) $registro->subtotal_snapshot);
        $snapshot['total'] = round($base + $itensTotal, 2);
        $solicitacao->update(['preco_estimado' => $snapshot['total'], 'calculo_snapshot' => $snapshot]);
    }

    private function urlDaProposta(string $token): string
    {
        $base = rtrim((string) config('app.public_url'), '/');
        $path = route('publico.proposta', $token, false);

        return $base !== '' ? $base.$path : url($path);
    }
}
