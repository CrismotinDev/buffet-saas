<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitacoes_orcamento', function (Blueprint $table) {
            $table->boolean('aceita_contato')->default(false)->after('observacoes');
        });
    }

    public function down(): void
    {
        Schema::table('solicitacoes_orcamento', function (Blueprint $table) {
            $table->dropColumn('aceita_contato');
        });
    }
};
