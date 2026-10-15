<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\CategoriaItem;
use App\Models\Empresa;
use App\Models\ItemCatalogo;
use App\Models\Pacote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PacoteController extends Controller
{
    public function index()
    {
        $empresa = $this->empresa();

        return Inertia::render('App/Pacotes/Index', [
            'pacotes' => Pacote::where('empresa_id', $empresa->id)->withCount(['categorias', 'itens'])->with(['categorias', 'itens.categorias'])->orderBy('ordem')->orderBy('nome')->get()->map(function (Pacote $pacote) {
                return [
                    ...$pacote->only(['id', 'nome', 'tipo_preco', 'preco_base', 'min_convidados', 'max_convidados', 'ativo', 'publicado', 'categorias_count', 'itens_count']),
                    'categorias' => $this->categoriasComItens($pacote),
                ];
            }),
        ]);
    }

    public function create()
    {
        $empresa = $this->empresa();

        return Inertia::render('App/Pacotes/Create', [
            'categorias' => CategoriaItem::where('empresa_id', $empresa->id)->where('ativo', true)->with(['itens' => fn ($q) => $q->where('ativo', true)->orderBy('nome')])->orderBy('ordem')->get(),
        ]);
    }

    public function edit(int $pacote)
    {
        $empresa = $this->empresa();
        $model = Pacote::where('empresa_id', $empresa->id)->with(['categorias', 'itens'])->findOrFail($pacote);

        return Inertia::render('App/Pacotes/Create', [
            'categorias' => CategoriaItem::where('empresa_id', $empresa->id)->where('ativo', true)->with(['itens' => fn ($q) => $q->where('ativo', true)->orderBy('nome')])->orderBy('ordem')->get(),
            'pacote' => [
                ...$model->only(['id', 'nome', 'descricao', 'tipo_preco', 'preco_base', 'min_convidados', 'max_convidados', 'publicado']),
                'categoria_ids' => $model->categorias->pluck('id')->values(),
                'item_ids' => $model->itens->pluck('id')->values(),
            ],
        ]);
    }

    public function show(int $pacote)
    {
        $model = Pacote::where('empresa_id', $this->empresa()->id)
            ->with(['categorias', 'itens'])
            ->findOrFail($pacote);

        return Inertia::render('App/Pacotes/Show', [
            'pacote' => [
                ...$model->only(['id', 'nome', 'descricao', 'tipo_preco', 'preco_base', 'min_convidados', 'max_convidados', 'publicado', 'ativo']),
                'categorias' => $model->categorias->map(fn ($categoria) => [
                    'id' => $categoria->id,
                    'nome' => $categoria->nome,
                    'min_escolhas' => $categoria->pivot->min_escolhas,
                    'max_escolhas' => $categoria->pivot->max_escolhas,
                    'obrigatoria' => $categoria->pivot->obrigatoria,
                    'itens' => $model->itens->filter(fn ($item) => (int) $item->pivot->categoria_id === $categoria->id)->map(fn ($item) => [
                        'id' => $item->id,
                        'nome' => $item->nome,
                        'descricao' => $item->descricao,
                        'unidade' => $item->unidade,
                    ])->values(),
                ])->values(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $empresa = $this->empresa();
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:160'], 'descricao' => ['nullable', 'string'],
            'tipo_preco' => ['required', 'in:por_pessoa,fixo'], 'preco_base' => ['required', 'numeric', 'min:0'],
            'min_convidados' => ['required', 'integer', 'min:1'], 'max_convidados' => ['nullable', 'integer', 'gte:min_convidados'],
            'publicado' => ['sometimes', 'boolean'], 'categoria_ids' => ['array'], 'categoria_ids.*' => ['integer'], 'item_ids' => ['array'], 'item_ids.*' => ['integer'],
        ]);

        $idsCategorias = CategoriaItem::where('empresa_id', $empresa->id)->whereIn('id', $data['categoria_ids'] ?? [])->pluck('id');
        $idsItens = ItemCatalogo::where('empresa_id', $empresa->id)->whereIn('id', $data['item_ids'] ?? [])->pluck('id');
        $pacote = DB::transaction(function () use ($data, $empresa, $idsCategorias, $idsItens) {
            $baseSlug = Str::slug($data['nome']);
            $slug = $baseSlug;
            $n = 2;
            while (Pacote::where('empresa_id', $empresa->id)->where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.($n++);
            }
            $pacote = Pacote::create([...collect($data)->except(['categoria_ids', 'item_ids'])->all(), 'empresa_id' => $empresa->id, 'slug' => $slug]);
            $pacote->categorias()->attach($idsCategorias->mapWithKeys(fn ($id) => [$id => ['empresa_id' => $empresa->id]])->all());
            $pacote->itens()->attach($idsItens->mapWithKeys(fn ($id) => [$id => ['empresa_id' => $empresa->id, 'categoria_id' => ItemCatalogo::find($id)->categoria_id]])->all());

            return $pacote;
        });

        return redirect()->route('app.pacotes.index')->with('success', 'Pacote criado.');
    }

    public function update(Request $request, int $pacote)
    {
        $empresa = $this->empresa();
        $model = Pacote::where('empresa_id', $empresa->id)->findOrFail($pacote);
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:160'], 'descricao' => ['nullable', 'string'],
            'tipo_preco' => ['required', 'in:por_pessoa,fixo'], 'preco_base' => ['required', 'numeric', 'min:0'],
            'min_convidados' => ['required', 'integer', 'min:1'], 'max_convidados' => ['nullable', 'integer', 'gte:min_convidados'],
            'publicado' => ['sometimes', 'boolean'], 'categoria_ids' => ['array'], 'categoria_ids.*' => ['integer'], 'item_ids' => ['array'], 'item_ids.*' => ['integer'],
        ]);
        $categoriaIds = CategoriaItem::where('empresa_id', $empresa->id)->whereIn('id', $data['categoria_ids'] ?? [])->pluck('id');
        $itemIds = ItemCatalogo::where('empresa_id', $empresa->id)->whereIn('id', $data['item_ids'] ?? [])->pluck('id');
        DB::transaction(function () use ($model, $data, $empresa, $categoriaIds, $itemIds): void {
            $model->update(collect($data)->except(['categoria_ids', 'item_ids'])->all());
            $model->categorias()->sync($categoriaIds->mapWithKeys(fn ($id) => [$id => ['empresa_id' => $empresa->id]])->all());
            $model->itens()->sync($itemIds->mapWithKeys(fn ($id) => [$id => ['empresa_id' => $empresa->id, 'categoria_id' => ItemCatalogo::find($id)->categoria_id]])->all());
        });

        return redirect()->route('app.pacotes.index')->with('success', 'Pacote atualizado.');
    }

    public function destroy(int $pacote)
    {
        $model = Pacote::where('empresa_id', $this->empresa()->id)->findOrFail($pacote);
        $model->delete();

        return redirect()->route('app.pacotes.index')->with('success', 'Pacote excluído.');
    }

    public function toggle(int $pacote)
    {
        $model = Pacote::where('empresa_id', $this->empresa()->id)->findOrFail($pacote);
        $model->update(['ativo' => ! $model->ativo]);

        return back()->with('success', $model->ativo ? 'Pacote ativado.' : 'Pacote desativado.');
    }

    private function empresa(): Empresa
    {
        return app('empresa.atual');
    }

    private function categoriasComItens(Pacote $pacote): array
    {
        $categorias = $pacote->categorias;
        $categoriaIds = $categorias->pluck('id');
        $itensPorCategoria = $pacote->itens->groupBy(function (ItemCatalogo $item) use ($categoriaIds) {
            $categoriaDoVinculo = (int) $item->pivot->categoria_id;

            if ($categoriaIds->contains($categoriaDoVinculo)) {
                return $categoriaDoVinculo;
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
}
