<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pacotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('nome');
            $table->string('slug');
            $table->text('descricao')->nullable();
            $table->string('tipo_preco', 20);
            $table->decimal('preco_base', 12, 2);
            $table->unsignedInteger('min_convidados')->default(1);
            $table->unsignedInteger('max_convidados')->nullable();
            $table->boolean('publicado')->default(false);
            $table->boolean('ativo')->default(true);
            $table->unsignedInteger('ordem')->default(0);
            $table->timestamps();
            $table->unique(['empresa_id', 'slug']);
            $table->index(['empresa_id', 'ativo', 'publicado']);
        });

        Schema::create('pacote_categorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('pacote_id')->constrained('pacotes')->cascadeOnDelete();
            $table->foreignId('categoria_id')->constrained('categorias_itens')->cascadeOnDelete();
            $table->unsignedInteger('min_escolhas')->default(0);
            $table->unsignedInteger('max_escolhas')->default(1);
            $table->boolean('obrigatoria')->default(false);
            $table->timestamps();
            $table->unique(['pacote_id', 'categoria_id']);
        });

        Schema::create('pacote_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('pacote_id')->constrained('pacotes')->cascadeOnDelete();
            $table->foreignId('categoria_id')->constrained('categorias_itens')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('itens_catalogo')->cascadeOnDelete();
            $table->boolean('permitido')->default(true);
            $table->boolean('incluido')->default(true);
            $table->decimal('eventual_sobretaxa', 12, 2)->nullable();
            $table->timestamps();
            $table->unique(['pacote_id', 'item_id']);
        });

        Schema::create('pacote_adicionais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('pacote_id')->constrained('pacotes')->cascadeOnDelete();
            $table->foreignId('adicional_id')->constrained('adicionais')->cascadeOnDelete();
            $table->boolean('ativo')->default(true);
            $table->boolean('obrigatorio')->default(false);
            $table->timestamps();
            $table->unique(['pacote_id', 'adicional_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pacote_adicionais');
        Schema::dropIfExists('pacote_itens');
        Schema::dropIfExists('pacote_categorias');
        Schema::dropIfExists('pacotes');
    }
};
