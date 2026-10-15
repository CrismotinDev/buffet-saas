<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SolicitacaoOrcamento extends Model
{
    protected $table = 'solicitacoes_orcamento';

    protected $fillable = ['empresa_id', 'pacote_id', 'modo_cardapio', 'protocolo', 'nome_contato', 'email', 'telefone', 'data_evento', 'convidados', 'tipo_local', 'endereco_local', 'observacoes', 'aceita_contato', 'status', 'retorno_em', 'preco_estimado', 'calculo_snapshot'];

    protected function casts(): array
    {
        return ['data_evento' => 'date', 'retorno_em' => 'date', 'preco_estimado' => 'decimal:2', 'aceita_contato' => 'boolean', 'calculo_snapshot' => 'array'];
    }

    public function pacote(): BelongsTo
    {
        return $this->belongsTo(Pacote::class);
    }

    public function itens(): HasMany
    {
        return $this->hasMany(SolicitacaoItem::class, 'solicitacao_id');
    }
}
