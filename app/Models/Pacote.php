<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pacote extends Model
{
    protected $fillable = ['empresa_id', 'nome', 'slug', 'descricao', 'tipo_preco', 'preco_base', 'min_convidados', 'max_convidados', 'publicado', 'ativo', 'ordem'];

    protected function casts(): array
    {
        return ['preco_base' => 'decimal:2', 'publicado' => 'boolean', 'ativo' => 'boolean'];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(CategoriaItem::class, 'pacote_categorias', 'pacote_id', 'categoria_id')->withPivot(['min_escolhas', 'max_escolhas', 'obrigatoria'])->withTimestamps();
    }

    public function itens(): BelongsToMany
    {
        return $this->belongsToMany(ItemCatalogo::class, 'pacote_itens', 'pacote_id', 'item_id')->withPivot(['categoria_id', 'permitido', 'incluido', 'eventual_sobretaxa'])->withTimestamps();
    }

    public function adicionais(): BelongsToMany
    {
        return $this->belongsToMany(Adicional::class, 'pacote_adicionais')->withPivot(['ativo', 'obrigatorio'])->withTimestamps();
    }
}
