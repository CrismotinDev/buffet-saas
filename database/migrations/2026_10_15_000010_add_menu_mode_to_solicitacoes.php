<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitacoes_orcamento', function (Blueprint $table) {
            $table->string('modo_cardapio', 20)->default('pacote')->after('pacote_id');
        });
    }

    public function down(): void
    {
        Schema::table('solicitacoes_orcamento', function (Blueprint $table) {
            $table->dropColumn('modo_cardapio');
        });
    }
};
