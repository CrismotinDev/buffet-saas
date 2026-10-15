<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('pacote_categorias')->orderBy('id')->each(function (object $vinculo): void {
            $quantidade = DB::table('pacote_itens')
                ->where('pacote_id', $vinculo->pacote_id)
                ->where('categoria_id', $vinculo->categoria_id)
                ->count();

            DB::table('pacote_categorias')->where('id', $vinculo->id)->update([
                'min_escolhas' => 0,
                'max_escolhas' => max(1, $quantidade),
            ]);
        });
    }

    public function down(): void
    {
        DB::table('pacote_categorias')->update(['min_escolhas' => 0, 'max_escolhas' => 1]);
    }
};
