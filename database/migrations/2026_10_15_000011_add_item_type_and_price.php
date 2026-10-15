<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('itens_catalogo', function (Blueprint $table) {
            $table->string('tipo_item', 20)->default('comida')->after('unidade');
            $table->decimal('preco_unitario', 12, 2)->nullable()->after('tipo_item');
        });

        Schema::table('solicitacao_itens', function (Blueprint $table) {
            $table->unsignedInteger('quantidade')->default(1)->after('nome_snapshot');
            $table->decimal('preco_unitario_snapshot', 12, 2)->default(0)->after('quantidade');
            $table->decimal('subtotal_snapshot', 12, 2)->default(0)->after('preco_unitario_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('solicitacao_itens', function (Blueprint $table) {
            $table->dropColumn(['quantidade', 'preco_unitario_snapshot', 'subtotal_snapshot']);
        });
        Schema::table('itens_catalogo', function (Blueprint $table) {
            $table->dropColumn(['tipo_item', 'preco_unitario']);
        });
    }
};
