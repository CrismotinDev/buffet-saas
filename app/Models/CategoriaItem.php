<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CategoriaItem extends Model
{
    protected $table = 'categorias_itens';

    protected $fillable = ['empresa_id', 'nome', 'slug', 'ordem', 'ativo'];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function itens(): BelongsToMany
    {
        return $this->belongsToMany(ItemCatalogo::class, 'categoria_item_catalogo', 'categoria_id', 'item_id')->withTimestamps();
    }
}
