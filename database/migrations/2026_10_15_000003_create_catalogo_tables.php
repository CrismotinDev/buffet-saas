<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('nome');
            $table->string('slug');
            $table->unsignedInteger('ordem')->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->unique(['empresa_id', 'slug']);
            $table->index(['empresa_id', 'ativo']);
        });

        Schema::create('itens_catalogo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('categoria_id')->constrained('categorias_itens')->restrictOnDelete();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->string('unidade')->default('unidade');
            $table->string('imagem_path')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->index(['empresa_id', 'categoria_id', 'ativo']);
        });

        Schema::create('adicionais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->string('tipo_cobranca', 24);
            $table->decimal('valor_unitario', 12, 2);
            $table->unsignedInteger('min_quantidade')->default(1);
            $table->unsignedInteger('max_quantidade')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->index(['empresa_id', 'ativo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adicionais');
        Schema::dropIfExists('itens_catalogo');
        Schema::dropIfExists('categorias_itens');
    }
};
