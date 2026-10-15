<?php

namespace Database\Seeders;

use App\Models\CategoriaItem;
use App\Models\Empresa;
use App\Models\ItemCatalogo;
use App\Models\Pacote;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoCatalogoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('Seeder demo bloqueado em produção.');

            return;
        }

        $empresa = Empresa::first();
        if (! $empresa) {
            $this->command?->warn('Cadastre uma empresa antes de executar este seeder.');

            return;
        }

        $categorias = collect([
            'Carnes' => ['Costela fogo de chão', 'Porco fogo de chão'],
            'Acompanhamentos' => ['Risoto', 'Maionese'],
        ])->mapWithKeys(function (array $nomes, string $nome) use ($empresa) {
            $categoria = CategoriaItem::firstOrCreate(['empresa_id' => $empresa->id, 'slug' => Str::slug($nome)], ['nome' => $nome]);
            foreach ($nomes as $itemNome) {
                $item = ItemCatalogo::firstOrCreate(['empresa_id' => $empresa->id, 'categoria_id' => $categoria->id, 'nome' => $itemNome], ['unidade' => 'porção']);
                $item->categorias()->syncWithoutDetaching([$categoria->id]);
            }

            return [$categoria->id => $categoria->fresh('itens')];
        });

        $pacote = Pacote::firstOrCreate(['empresa_id' => $empresa->id, 'slug' => 'tradicional'], [
            'nome' => 'Tradicional', 'tipo_preco' => 'por_pessoa', 'preco_base' => 75,
            'min_convidados' => 30, 'publicado' => true,
        ]);

        foreach ($categorias as $categoria) {
            $pacote->categorias()->syncWithoutDetaching([$categoria->id => [
                'empresa_id' => $empresa->id, 'min_escolhas' => 2, 'max_escolhas' => 2, 'obrigatoria' => true,
            ]]);
            $pacote->itens()->syncWithoutDetaching($categoria->itens->mapWithKeys(fn ($item) => [$item->id => [
                'empresa_id' => $empresa->id, 'categoria_id' => $categoria->id,
            ]])->all());
        }
    }
}
