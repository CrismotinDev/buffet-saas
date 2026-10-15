<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitacoes_orcamento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('pacote_id')->constrained('pacotes')->restrictOnDelete();
            $table->string('protocolo', 32)->unique();
            $table->string('nome_contato');
            $table->string('email');
            $table->string('telefone', 40);
            $table->date('data_evento');
            $table->unsignedInteger('convidados');
            $table->string('tipo_local', 20);
            $table->string('endereco_local')->nullable();
            $table->text('observacoes')->nullable();
            $table->string('status', 30)->default('recebida');
            $table->decimal('preco_estimado', 12, 2);
            $table->jsonb('calculo_snapshot');
            $table->timestamps();
            $table->index(['empresa_id', 'status']);
        });

        Schema::create('solicitacao_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitacao_id')->constrained('solicitacoes_orcamento')->cascadeOnDelete();
            $table->foreignId('categoria_id')->constrained('categorias_itens')->restrictOnDelete();
            $table->foreignId('item_id')->constrained('itens_catalogo')->restrictOnDelete();
            $table->string('nome_snapshot');
            $table->string('categoria_snapshot');
            $table->timestamps();
            $table->unique(['solicitacao_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitacao_itens');
        Schema::dropIfExists('solicitacoes_orcamento');
    }
};
