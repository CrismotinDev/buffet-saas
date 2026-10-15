<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Adicional;
use App\Models\CategoriaItem;
use App\Models\Empresa;
use App\Models\ItemCatalogo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CatalogoController extends Controller
{
    public function index()
    {
        $empresa = $this->empresa();

        return Inertia::render('App/Catalogo/Index', [
            'categorias' => CategoriaItem::where('empresa_id', $empresa->id)->withCount('itens')->orderBy('ordem')->orderBy('nome')->get(),
            'itens' => ItemCatalogo::where('empresa_id', $empresa->id)->with('categorias:id,nome')->orderBy('nome')->get(),
            'adicionais' => Adicional::where('empresa_id', $empresa->id)->orderBy('nome')->get(),
        ]);
    }

    public function categoriaStore(Request $request)
    {
        $empresa = $this->empresa();
        $data = $request->validate(['nome' => ['required', 'string', 'max:120'], 'ordem' => ['nullable', 'integer', 'min:0']]);
        CategoriaItem::create([...$data, 'empresa_id' => $empresa->id, 'slug' => $this->uniqueSlug(CategoriaItem::class, $data['nome'], $empresa->id)]);

        return back()->with('success', 'Categoria criada.');
    }

    public function categoriaUpdate(Request $request, int $categoria)
    {
        $model = $this->categoria($categoria);
        $data = $request->validate(['nome' => ['required', 'string', 'max:120'], 'ordem' => ['nullable', 'integer', 'min:0'], 'ativo' => ['sometimes', 'boolean']]);
        $model->update($data + ['slug' => $this->uniqueSlug(CategoriaItem::class, $data['nome'], $model->empresa_id, $model->id)]);

        return back()->with('success', 'Categoria atualizada.');
    }

    public function categoriaDestroy(int $categoria)
    {
        $model = $this->categoria($categoria);
        if ($model->itens()->exists() || ItemCatalogo::where('categoria_id', $model->id)->exists()) {
            throw ValidationException::withMessages(['categoria' => 'Remova ou mova os itens antes de excluir a categoria.']);
        }
        $model->delete();

        return back()->with('success', 'Categoria removida.');
    }

    public function itemStore(Request $request)
    {
        $empresa = $this->empresa();
        $data = $request->validate(['categoria_ids' => ['required', 'array', 'min:1'], 'categoria_ids.*' => ['integer'], 'nome' => ['required', 'string', 'max:160'], 'descricao' => ['nullable', 'string'], 'unidade' => ['required', 'string', 'max:40']]);
        $categoriaIds = CategoriaItem::where('empresa_id', $empresa->id)->whereIn('id', $data['categoria_ids'])->pluck('id');
        abort_unless($categoriaIds->count() === count(array_unique($data['categoria_ids'])), 422, 'Uma ou mais categorias são inválidas.');
        $item = ItemCatalogo::create([...$data, 'categoria_id' => $categoriaIds->first(), 'empresa_id' => $empresa->id]);
        $item->categorias()->sync($categoriaIds);

        return back()->with('success', 'Item criado.');
    }

    public function itemUpdate(Request $request, int $item)
    {
        $model = $this->item($item);
        $data = $request->validate(['categoria_ids' => ['required', 'array', 'min:1'], 'categoria_ids.*' => ['integer'], 'nome' => ['required', 'string', 'max:160'], 'descricao' => ['nullable', 'string'], 'unidade' => ['required', 'string', 'max:40'], 'ativo' => ['sometimes', 'boolean']]);
        $categoriaIds = CategoriaItem::where('empresa_id', $model->empresa_id)->whereIn('id', $data['categoria_ids'])->pluck('id');
        abort_unless($categoriaIds->count() === count(array_unique($data['categoria_ids'])), 422, 'Uma ou mais categorias são inválidas.');
        $model->update([...$data, 'categoria_id' => $categoriaIds->first()]);
        $model->categorias()->sync($categoriaIds);

        return back()->with('success', 'Item atualizado.');
    }

    public function itemDestroy(int $item)
    {
        $this->item($item)->delete();

        return back()->with('success', 'Item removido.');
    }

    public function adicionalStore(Request $request)
    {
        $data = $request->validate(['nome' => ['required', 'string', 'max:160'], 'descricao' => ['nullable', 'string'], 'tipo_cobranca' => ['required', 'in:por_pessoa,por_evento,por_unidade'], 'valor_unitario' => ['required', 'numeric', 'min:0'], 'min_quantidade' => ['required', 'integer', 'min:1'], 'max_quantidade' => ['nullable', 'integer', 'gte:min_quantidade']]);
        Adicional::create($data + ['empresa_id' => $this->empresa()->id]);

        return back()->with('success', 'Adicional criado.');
    }

    public function adicionalUpdate(Request $request, int $adicional)
    {
        $model = Adicional::where('empresa_id', $this->empresa()->id)->findOrFail($adicional);
        $data = $request->validate(['nome' => ['required', 'string', 'max:160'], 'descricao' => ['nullable', 'string'], 'tipo_cobranca' => ['required', 'in:por_pessoa,por_evento,por_unidade'], 'valor_unitario' => ['required', 'numeric', 'min:0'], 'min_quantidade' => ['required', 'integer', 'min:1'], 'max_quantidade' => ['nullable', 'integer', 'gte:min_quantidade'], 'ativo' => ['sometimes', 'boolean']]);
        $model->update($data);

        return back()->with('success', 'Adicional atualizado.');
    }

    public function adicionalDestroy(int $adicional)
    {
        Adicional::where('empresa_id', $this->empresa()->id)->findOrFail($adicional)->delete();

        return back()->with('success', 'Adicional removido.');
    }

    private function empresa(): Empresa
    {
        return app('empresa.atual');
    }

    private function categoria(int $id): CategoriaItem
    {
        return CategoriaItem::where('empresa_id', $this->empresa()->id)->findOrFail($id);
    }

    private function item(int $id): ItemCatalogo
    {
        return ItemCatalogo::where('empresa_id', $this->empresa()->id)->findOrFail($id);
    }

    private function uniqueSlug(string $model, string $name, int $empresaId, ?int $ignore = null): string
    {
        $base = Str::slug($name) ?: 'item';
        $slug = $base;
        $n = 2;
        while ($model::where('empresa_id', $empresaId)->where('slug', $slug)->when($ignore, fn ($q) => $q->whereKeyNot($ignore))->exists()) {
            $slug = $base.'-'.($n++);
        }

        return $slug;
    }
}
