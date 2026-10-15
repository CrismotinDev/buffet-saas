<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ItemCatalogo extends Model
{
    protected $table = 'itens_catalogo';

    protected $fillable = ['empresa_id', 'categoria_id', 'nome', 'descricao', 'unidade', 'tipo_item', 'preco_unitario', 'referencia_consumo', 'imagem_path', 'ativo'];

    protected function casts(): array
    {
        return ['ativo' => 'boolean', 'preco_unitario' => 'decimal:2'];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaItem::class, 'categoria_id');
    }

    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(CategoriaItem::class, 'categoria_item_catalogo', 'item_id', 'categoria_id')->withTimestamps();
    }
}
