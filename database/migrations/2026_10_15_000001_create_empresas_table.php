<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nome_fantasia');
            $table->string('razao_social')->nullable();
            $table->string('documento', 32)->nullable();
            $table->string('slug')->unique();
            $table->string('email');
            $table->string('telefone', 32)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('timezone')->default('America/Sao_Paulo');
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index(['ativo', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
