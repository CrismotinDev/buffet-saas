<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Adicional extends Model
{
    protected $fillable = ['empresa_id', 'nome', 'descricao', 'tipo_cobranca', 'valor_unitario', 'min_quantidade', 'max_quantidade', 'ativo'];

    protected function casts(): array
    {
        return ['valor_unitario' => 'decimal:2', 'ativo' => 'boolean'];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
}
