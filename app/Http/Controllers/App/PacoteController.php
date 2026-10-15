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
            'pacotes' => Pacote::where('empresa_id', $empresa->id)->withCount(['categorias', 'itens'])->orderBy('ordem')->orderBy('nome')->get(),
        ]);
    }

    public function create()
    {
        $empresa = $this->empresa();

        return Inertia::render('App/Pacotes/Create', [
            'categorias' => CategoriaItem::where('empresa_id', $empresa->id)->where('ativo', true)->with(['itens' => fn ($q) => $q->where('ativo', true)->orderBy('nome')])->orderBy('ordem')->get(),
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
}
