<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemCatalogo extends Model
{
    protected $table = 'itens_catalogo';

    protected $fillable = ['empresa_id', 'categoria_id', 'nome', 'descricao', 'unidade', 'imagem_path', 'ativo'];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaItem::class, 'categoria_id');
    }
}
