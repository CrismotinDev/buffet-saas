<?php

namespace Tests\Feature;

use App\Models\CategoriaItem;
use App\Models\Empresa;
use App\Models\ItemCatalogo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogoTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_catalog_item_in_active_company(): void
    {
        [$user, $empresa] = $this->membership('catalogo@example.com', 'Empresa A', 'empresa-a');
        $categoria = CategoriaItem::create(['empresa_id' => $empresa->id, 'nome' => 'Carnes', 'slug' => 'carnes']);

        $this->actingAs($user)->post('/app/catalogo/itens', [
            'categoria_ids' => [$categoria->id], 'nome' => 'Costela fogo de chão', 'unidade' => 'porção',
        ])->assertRedirect();

        $this->assertDatabaseHas('itens_catalogo', ['empresa_id' => $empresa->id, 'nome' => 'Costela fogo de chão']);
    }

    public function test_catalog_page_loads_all_catalog_tables(): void
    {
        [$user] = $this->membership('page@example.com', 'Empresa A', 'empresa-a');

        $this->actingAs($user)->get('/app/catalogo')
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page->component('App/Catalogo/Index'));
    }

    public function test_catalog_item_from_another_company_is_rejected(): void
    {
        [$user, $empresa] = $this->membership('catalogo@example.com', 'Empresa A', 'empresa-a');
        [, $other] = $this->membership('other@example.com', 'Empresa B', 'empresa-b');
        $categoria = CategoriaItem::create(['empresa_id' => $other->id, 'nome' => 'Carnes', 'slug' => 'carnes']);

        $this->actingAs($user)->post('/app/catalogo/itens', [
            'categoria_ids' => [$categoria->id], 'nome' => 'Item indevido', 'unidade' => 'unidade',
        ])->assertStatus(422);

        $this->assertDatabaseMissing('itens_catalogo', ['empresa_id' => $empresa->id, 'nome' => 'Item indevido']);
    }

    public function test_same_item_can_belong_to_multiple_categories(): void
    {
        [$user, $empresa] = $this->membership('multi@example.com', 'Empresa A', 'empresa-a');
        $carnes = CategoriaItem::create(['empresa_id' => $empresa->id, 'nome' => 'Carnes', 'slug' => 'carnes']);
        $especiais = CategoriaItem::create(['empresa_id' => $empresa->id, 'nome' => 'Especiais', 'slug' => 'especiais']);

        $this->actingAs($user)->post('/app/catalogo/itens', [
            'categoria_ids' => [$carnes->id, $especiais->id], 'nome' => 'Pernil de Carneiro', 'unidade' => 'porção',
        ])->assertRedirect();

        $item = ItemCatalogo::where('empresa_id', $empresa->id)->where('nome', 'Pernil de Carneiro')->firstOrFail();
        $this->assertCount(2, $item->categorias);
        $this->assertDatabaseHas('categoria_item_catalogo', ['categoria_id' => $carnes->id, 'item_id' => $item->id]);
        $this->assertDatabaseHas('categoria_item_catalogo', ['categoria_id' => $especiais->id, 'item_id' => $item->id]);
    }

    public function test_deleting_category_with_items_is_prevented_by_database(): void
    {
        [$user, $empresa] = $this->membership('catalogo@example.com', 'Empresa A', 'empresa-a');
        $categoria = CategoriaItem::create(['empresa_id' => $empresa->id, 'nome' => 'Carnes', 'slug' => 'carnes']);
        ItemCatalogo::create(['empresa_id' => $empresa->id, 'categoria_id' => $categoria->id, 'nome' => 'Costela', 'unidade' => 'porção']);

        $this->actingAs($user)->delete("/app/catalogo/categorias/{$categoria->id}")->assertSessionHasErrors('categoria');
    }

    public function test_user_can_create_a_package_with_company_catalog_records(): void
    {
        [$user, $empresa] = $this->membership('package@example.com', 'Empresa A', 'empresa-a');
        $categoria = CategoriaItem::create(['empresa_id' => $empresa->id, 'nome' => 'Carnes', 'slug' => 'carnes']);
        $item = ItemCatalogo::create(['empresa_id' => $empresa->id, 'categoria_id' => $categoria->id, 'nome' => 'Costela', 'unidade' => 'porção']);

        $this->actingAs($user)->post('/app/pacotes', [
            'nome' => 'Tradicional', 'tipo_preco' => 'por_pessoa', 'preco_base' => '75.00',
            'min_convidados' => 30, 'categoria_ids' => [$categoria->id], 'item_ids' => [$item->id],
        ])->assertRedirect('/app/pacotes');

        $this->assertDatabaseHas('pacotes', ['empresa_id' => $empresa->id, 'slug' => 'tradicional', 'preco_base' => 75]);
        $this->assertDatabaseHas('pacote_itens', ['item_id' => $item->id, 'empresa_id' => $empresa->id]);
    }

    private function membership(string $email, string $name, string $slug): array
    {
        $user = User::factory()->create(['email' => $email]);
        $empresa = Empresa::create(['nome_fantasia' => $name, 'slug' => $slug, 'email' => $email]);
        $empresa->usuarios()->attach($user, ['papel' => 'admin', 'ativo' => true]);

        return [$user, $empresa];
    }
}
