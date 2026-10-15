<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\ItemCatalogo;
use App\Models\Pacote;
use App\Models\SolicitacaoOrcamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CardapioController extends Controller
{
    public function empresa(Empresa $empresa)
    {
        abort_unless($empresa->ativo, 404);

        $pacotes = Pacote::where('empresa_id', $empresa->id)
            ->where('ativo', true)
            ->where('publicado', true)
            ->with(['itens:id,nome'])
            ->withCount('itens')
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get(['id', 'nome', 'slug', 'descricao', 'tipo_preco', 'preco_base', 'min_convidados', 'max_convidados']);

        return Inertia::render('Publico/Cardapio/Empresa', [
            'empresa' => $empresa->only(['slug', 'nome_fantasia', 'logo_path', 'telefone', 'email']),
            'pacotes' => $pacotes,
        ]);
    }

    public function pacote(Empresa $empresa, string $pacote)
    {
        abort_unless($empresa->ativo, 404);

        $model = Pacote::where('empresa_id', $empresa->id)
            ->where('slug', $pacote)
            ->where('ativo', true)
            ->where('publicado', true)
            ->with(['categorias', 'itens.categorias'])
            ->withCount('itens')
            ->firstOrFail();

        return Inertia::render('Publico/Cardapio/Pacote', [
            'empresa' => $empresa->only(['slug', 'nome_fantasia', 'logo_path', 'telefone', 'email']),
            'pacote' => [
                ...$model->only(['id', 'nome', 'slug', 'descricao', 'tipo_preco', 'preco_base', 'min_convidados', 'max_convidados', 'itens_count']),
                'categorias' => $this->categoriasComItens($model),
                'itens_livres' => ItemCatalogo::where('empresa_id', $empresa->id)->where('ativo', true)->with('categoria:id,nome')->orderBy('nome')->get(['id', 'nome', 'descricao', 'unidade', 'tipo_item', 'preco_unitario', 'referencia_consumo', 'categoria_id'])->map(fn ($item) => ['id' => $item->id, 'nome' => $item->nome, 'descricao' => $item->descricao, 'unidade' => $item->unidade, 'tipo_item' => $item->tipo_item, 'preco_unitario' => $item->preco_unitario, 'referencia_consumo' => $item->referencia_consumo, 'categoria' => $item->categoria?->nome])->values(),
            ],
        ]);
    }

    public function personalizado(Empresa $empresa)
    {
        abort_unless($empresa->ativo, 404);
        $referencia = Pacote::where('empresa_id', $empresa->id)->where('ativo', true)->where('publicado', true)->firstOrFail();
        $itens = ItemCatalogo::where('empresa_id', $empresa->id)->where('ativo', true)->with('categoria:id,nome')->orderBy('nome')->get(['id', 'nome', 'descricao', 'unidade', 'tipo_item', 'preco_unitario', 'referencia_consumo', 'categoria_id']);

        return Inertia::render('Publico/Cardapio/Pacote', [
            'personalizado' => true,
            'empresa' => $empresa->only(['slug', 'nome_fantasia', 'logo_path', 'telefone', 'email']),
            'pacote' => [
                'id' => $referencia->id, 'nome' => 'Cardápio personalizado', 'slug' => $referencia->slug, 'descricao' => 'Monte uma combinação exclusiva para o seu evento.', 'tipo_preco' => 'por_evento', 'preco_base' => 0, 'min_convidados' => 30, 'max_convidados' => null, 'itens_count' => 0, 'categorias' => [],
                'itens_livres' => $itens->map(fn ($item) => ['id' => $item->id, 'nome' => $item->nome, 'descricao' => $item->descricao, 'unidade' => $item->unidade, 'tipo_item' => $item->tipo_item, 'preco_unitario' => $item->preco_unitario, 'referencia_consumo' => $item->referencia_consumo, 'categoria' => $item->categoria?->nome])->values(),
            ],
        ]);
    }

    public function solicitarPersonalizado(Request $request, Empresa $empresa)
    {
        abort_unless($empresa->ativo, 404);
        $referencia = Pacote::where('empresa_id', $empresa->id)->where('ativo', true)->where('publicado', true)->firstOrFail();
        $request->merge(['modo_cardapio' => 'personalizado']);

        return $this->solicitar($request, $empresa, $referencia->slug);
    }

    public function solicitar(Request $request, Empresa $empresa, string $pacote)
    {
        abort_unless($empresa->ativo, 404);
        $model = Pacote::where('empresa_id', $empresa->id)->where('slug', $pacote)->where('ativo', true)->where('publicado', true)->with(['categorias', 'itens.categorias'])->firstOrFail();
        $data = $request->validate([
            'nome_contato' => ['required', 'string', 'max:160'], 'email' => ['required', 'email', 'max:160'], 'telefone' => ['required', 'string', 'max:40'],
            'data_evento' => ['required', 'date', 'after_or_equal:today'], 'convidados' => ['required', 'integer', 'min:1'],
            'tipo_local' => ['required', 'in:interno,externo'], 'endereco_local' => ['nullable', 'string', 'max:255'], 'observacoes' => ['nullable', 'string', 'max:2000'], 'aceita_contato' => ['required', 'boolean'], 'modo_cardapio' => ['nullable', 'in:pacote,personalizado'],
            'item_ids' => ['nullable', 'array'], 'item_ids.*' => ['integer', 'distinct'], 'item_quantidades' => ['nullable', 'array'], 'item_quantidades.*' => ['integer', 'min:1', 'max:9999'],
        ]);
        $modo = $data['modo_cardapio'] ?? 'pacote';
        $data['modo_cardapio'] = $modo;
        $minConvidados = $modo === 'personalizado' ? 30 : $model->min_convidados;
        if ($data['convidados'] < $minConvidados || ($model->max_convidados && $data['convidados'] > $model->max_convidados)) {
            throw ValidationException::withMessages(['convidados' => 'A quantidade de convidados está fora dos limites deste pacote.']);
        }
        $categorias = $model->categorias;
        if ($modo === 'personalizado') {
            if (count($data['item_ids'] ?? []) < 1) {
                throw ValidationException::withMessages(['item_ids' => 'Selecione ao menos um item para montar seu cardápio.']);
            }
            $itens = ItemCatalogo::where('empresa_id', $empresa->id)->where('ativo', true)->whereIn('id', $data['item_ids'])->with('categorias')->get();
            if ($itens->count() !== count($data['item_ids'])) {
                throw ValidationException::withMessages(['item_ids' => 'Um ou mais itens selecionados não estão disponíveis.']);
            }
        } else {
            $itens = $model->itens;
            $bebidaIds = collect($data['item_ids'] ?? [])->diff($itens->pluck('id'))->values()->all();
            if ($bebidaIds) {
                $bebidasSelecionadas = ItemCatalogo::where('empresa_id', $empresa->id)->where('ativo', true)->whereIn('id', $bebidaIds)->with('categorias')->get();
                if ($bebidasSelecionadas->count() !== count($bebidaIds) || $bebidasSelecionadas->contains(fn ($item) => $item->tipo_item !== 'bebida')) {
                    throw ValidationException::withMessages(['item_ids' => 'Selecione apenas bebidas disponíveis para adicionar ao pacote.']);
                }
                $itens = $itens->concat($bebidasSelecionadas);
            }
        }
        $base = $modo === 'personalizado' ? 0 : ($model->tipo_preco === 'por_pessoa' ? $data['convidados'] * (float) $model->preco_base : (float) $model->preco_base);
        $quantidades = $data['item_quantidades'] ?? [];
        $bebidas = $itens->filter(fn ($item) => $item->tipo_item === 'bebida' && array_key_exists($item->id, $quantidades));
        $bebidasTotal = $bebidas->sum(fn ($item) => ((int) ($quantidades[$item->id] ?? 1)) * (float) ($item->preco_unitario ?? 0));
        $total = $base + $bebidasTotal;
        $snapshot = ['modo_cardapio' => $modo, 'pacote' => ['id' => $model->id, 'nome' => $model->nome, 'tipo_preco' => $model->tipo_preco, 'preco_base' => $model->preco_base], 'convidados' => $data['convidados'], 'itens' => $itens->map(fn ($item) => ['id' => $item->id, 'nome' => $item->nome, 'quantidade' => (int) ($quantidades[$item->id] ?? 1), 'preco_unitario' => $item->preco_unitario, 'subtotal' => round(((int) ($quantidades[$item->id] ?? 1)) * (float) ($item->preco_unitario ?? 0), 2)])->values()->all(), 'base' => round($base, 2), 'bebidas_total' => round($bebidasTotal, 2), 'total' => round($total, 2)];
        $solicitacao = DB::transaction(function () use ($data, $empresa, $model, $total, $snapshot, $itens, $categorias, $quantidades) {
            $solicitacao = SolicitacaoOrcamento::create([...collect($data)->except(['item_ids', 'item_quantidades'])->all(), 'empresa_id' => $empresa->id, 'pacote_id' => $model->id, 'protocolo' => strtoupper(Str::random(10)), 'preco_estimado' => $total, 'calculo_snapshot' => $snapshot]);
            $solicitacao->itens()->createMany($itens->map(fn ($item) => ['categoria_id' => $this->categoriaDoItem($item, $categorias), 'item_id' => $item->id, 'nome_snapshot' => $item->nome, 'quantidade' => (int) ($quantidades[$item->id] ?? 1), 'preco_unitario_snapshot' => (float) ($item->preco_unitario ?? 0), 'subtotal_snapshot' => round(((int) ($quantidades[$item->id] ?? 1)) * (float) ($item->preco_unitario ?? 0), 2), 'categoria_snapshot' => $categorias->firstWhere('id', $this->categoriaDoItem($item, $categorias))?->nome ?? $item->categorias->first()?->nome ?? 'Personalizado'])->all());

            return $solicitacao;
        });

        return redirect()->route('publico.solicitacao.confirmacao', $solicitacao->protocolo);
    }

    public function confirmacao(string $protocolo)
    {
        $solicitacao = SolicitacaoOrcamento::where('protocolo', $protocolo)
            ->with(['pacote.empresa', 'itens'])
            ->firstOrFail();

        return Inertia::render('Publico/Cardapio/Confirmacao', [
            'relatorio' => [
                'protocolo' => $solicitacao->protocolo,
                'nome' => $solicitacao->nome_contato,
                'empresa' => $solicitacao->pacote->empresa->nome_fantasia,
                'pacote' => $solicitacao->pacote->nome,
                'modo_cardapio' => $solicitacao->modo_cardapio,
                'data_evento' => $solicitacao->data_evento?->format('d/m/Y'),
                'convidados' => $solicitacao->convidados,
                'tipo_local' => $solicitacao->tipo_local,
                'endereco_local' => $solicitacao->endereco_local,
                'preco_base' => $solicitacao->calculo_snapshot['pacote']['preco_base'] ?? null,
                'tipo_preco' => $solicitacao->calculo_snapshot['pacote']['tipo_preco'] ?? null,
                'preco_estimado' => $solicitacao->preco_estimado,
                'bebidas_total' => $solicitacao->calculo_snapshot['bebidas_total'] ?? 0,
                'aceita_contato' => $solicitacao->aceita_contato,
                'itens' => $solicitacao->itens->map(fn ($item) => [
                    'nome' => $item->nome_snapshot,
                    'categoria' => $item->categoria_snapshot,
                    'quantidade' => $item->quantidade,
                    'preco_unitario' => $item->preco_unitario_snapshot,
                    'subtotal' => $item->subtotal_snapshot,
                ])->values(),
            ],
        ]);
    }

    private function categoriasComItens(Pacote $pacote): array
    {
        $categorias = $pacote->categorias;
        $categoriaIds = $categorias->pluck('id');
        $itensPorCategoria = $pacote->itens->groupBy(function ($item) use ($categoriaIds) {
            $vinculo = (int) $item->pivot->categoria_id;

            if ($categoriaIds->contains($vinculo)) {
                return $vinculo;
            }

            return $item->categorias->pluck('id')->first(fn ($id) => $categoriaIds->contains($id)) ?? $categoriaIds->first();
        });

        return $categorias->map(fn ($categoria) => [
            'id' => $categoria->id,
            'nome' => $categoria->nome,
            'min_escolhas' => $categoria->pivot->min_escolhas,
            'max_escolhas' => $categoria->pivot->max_escolhas,
            'obrigatoria' => $categoria->pivot->obrigatoria,
            'itens' => $itensPorCategoria->get($categoria->id, collect())->map(fn ($item) => [
                'id' => $item->id,
                'nome' => $item->nome,
                'descricao' => $item->descricao,
                'unidade' => $item->unidade,
            ])->values(),
        ])->values()->all();
    }

    private function categoriaDoItem($item, $categorias): int
    {
        $ids = $categorias->pluck('id');
        $pivotCategoriaId = $item->pivot?->categoria_id ?: $item->categoria_id;
        if ($pivotCategoriaId && $ids->contains((int) $pivotCategoriaId)) {
            return (int) $pivotCategoriaId;
        }

        return (int) ($item->categorias->pluck('id')->first(fn ($id) => $ids->contains($id)) ?? $item->categoria_id ?? $item->categorias->pluck('id')->first() ?? 0);
    }
}
