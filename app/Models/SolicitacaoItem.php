<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitacaoItem extends Model
{
    protected $table = 'solicitacao_itens';

    protected $fillable = ['solicitacao_id', 'categoria_id', 'item_id', 'nome_snapshot', 'quantidade', 'preco_unitario_snapshot', 'subtotal_snapshot', 'categoria_snapshot'];

    protected function casts(): array
    {
        return ['quantidade' => 'integer', 'preco_unitario_snapshot' => 'decimal:2', 'subtotal_snapshot' => 'decimal:2'];
    }

    public function solicitacao(): BelongsTo
    {
        return $this->belongsTo(SolicitacaoOrcamento::class, 'solicitacao_id');
    }
}
