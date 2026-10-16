<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitacoes_orcamento', function (Blueprint $table) {
            $table->uuid('proposta_token')->nullable()->unique()->after('protocolo');
            $table->decimal('preco_proposto', 12, 2)->nullable()->after('preco_estimado');
            $table->decimal('desconto_proposto', 12, 2)->default(0)->after('preco_proposto');
            $table->date('validade_proposta')->nullable()->after('desconto_proposto');
            $table->text('observacoes_proposta')->nullable()->after('validade_proposta');
            $table->timestamp('proposta_enviada_em')->nullable()->after('observacoes_proposta');
        });
    }

    public function down(): void
    {
        Schema::table('solicitacoes_orcamento', function (Blueprint $table) {
            $table->dropUnique(['proposta_token']);
            $table->dropColumn(['proposta_token', 'preco_proposto', 'desconto_proposto', 'validade_proposta', 'observacoes_proposta', 'proposta_enviada_em']);
        });
    }
};
