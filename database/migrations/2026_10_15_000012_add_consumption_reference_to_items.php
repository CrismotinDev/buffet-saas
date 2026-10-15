<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('itens_catalogo', function (Blueprint $table) {
            $table->string('referencia_consumo', 160)->nullable()->after('preco_unitario');
        });
    }

    public function down(): void
    {
        Schema::table('itens_catalogo', function (Blueprint $table) {
            $table->dropColumn('referencia_consumo');
        });
    }
};
