<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categoria_item_catalogo', function (Blueprint $table) {
            $table->foreignId('categoria_id')->constrained('categorias_itens')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('itens_catalogo')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['categoria_id', 'item_id']);
        });

        DB::table('itens_catalogo')->orderBy('id')->each(function (object $item): void {
            DB::table('categoria_item_catalogo')->insert([
                'categoria_id' => $item->categoria_id,
                'item_id' => $item->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categoria_item_catalogo');
    }
};
