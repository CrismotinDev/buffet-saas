<?php

namespace Tests\Feature;

use App\Models\CategoriaItem;
use App\Models\Empresa;
use App\Models\ItemCatalogo;
use App\Models\Pacote;
use App\Models\SolicitacaoOrcamento;
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

    public function test_package_list_includes_dishes_for_modal_view(): void
    {
        [$user, $empresa] = $this->membership('show-package@example.com', 'Empresa A', 'empresa-a');
        $categoria = CategoriaItem::create(['empresa_id' => $empresa->id, 'nome' => 'Carnes', 'slug' => 'carnes']);
        $categoriaEspecial = CategoriaItem::create(['empresa_id' => $empresa->id, 'nome' => 'Especiais', 'slug' => 'especiais']);
        $item = ItemCatalogo::create(['empresa_id' => $empresa->id, 'categoria_id' => $categoria->id, 'nome' => 'Costela', 'unidade' => 'porção']);
        $item->categorias()->attach([$categoria->id, $categoriaEspecial->id]);
        $pacote = Pacote::create(['empresa_id' => $empresa->id, 'nome' => 'Tradicional', 'slug' => 'tradicional', 'tipo_preco' => 'por_pessoa', 'preco_base' => 75, 'min_convidados' => 30]);
        $pacote->categorias()->attach($categoriaEspecial, ['empresa_id' => $empresa->id, 'min_escolhas' => 1, 'max_escolhas' => 2]);
        $pacote->itens()->attach($item, ['empresa_id' => $empresa->id, 'categoria_id' => $categoria->id]);

        $this->actingAs($user)->get('/app/pacotes')
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page->component('App/Pacotes/Index')->where('pacotes.0.categorias.0.itens.0.nome', 'Costela'));
    }

    public function test_user_can_edit_and_delete_a_package_in_active_company(): void
    {
        [$user, $empresa] = $this->membership('manage-package@example.com', 'Empresa A', 'empresa-a');
        $pacote = Pacote::create(['empresa_id' => $empresa->id, 'nome' => 'Tradicional', 'slug' => 'tradicional', 'tipo_preco' => 'por_pessoa', 'preco_base' => 75, 'min_convidados' => 30]);

        $this->actingAs($user)->put("/app/pacotes/{$pacote->id}", [
            'nome' => 'Tradicional Especial', 'tipo_preco' => 'por_pessoa', 'preco_base' => 85,
            'min_convidados' => 40, 'categoria_ids' => [], 'item_ids' => [],
        ])->assertRedirect('/app/pacotes');

        $this->assertDatabaseHas('pacotes', ['id' => $pacote->id, 'nome' => 'Tradicional Especial', 'preco_base' => 85]);
        $this->actingAs($user)->delete("/app/pacotes/{$pacote->id}")->assertRedirect('/app/pacotes');
        $this->assertDatabaseMissing('pacotes', ['id' => $pacote->id]);
    }

    public function test_published_package_is_available_on_public_cardapio_link(): void
    {
        [$user, $empresa] = $this->membership('public-cardapio@example.com', 'Empresa A', 'empresa-a');
        $pacote = Pacote::create([
            'empresa_id' => $empresa->id, 'nome' => 'Pacote Tradicional', 'slug' => 'pacote-tradicional',
            'tipo_preco' => 'por_pessoa', 'preco_base' => 75, 'min_convidados' => 30, 'publicado' => true, 'ativo' => true,
        ]);
        $categoria = CategoriaItem::create(['empresa_id' => $empresa->id, 'nome' => 'Acompanhamentos', 'slug' => 'acompanhamentos']);
        $item = ItemCatalogo::create(['empresa_id' => $empresa->id, 'categoria_id' => $categoria->id, 'nome' => 'Risoto', 'unidade' => 'porção']);
        $item->categorias()->attach($categoria);
        $pacote->categorias()->attach($categoria, ['empresa_id' => $empresa->id, 'min_escolhas' => 1, 'max_escolhas' => 1]);
        $pacote->itens()->attach($item, ['empresa_id' => $empresa->id, 'categoria_id' => $categoria->id]);

        $this->get('/e/empresa-a')
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page->component('Publico/Cardapio/Empresa')->where('empresa.slug', 'empresa-a')->where('pacotes.0.slug', $pacote->slug));

        $this->get('/e/empresa-a/pacotes/pacote-tradicional')
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page->component('Publico/Cardapio/Pacote')->where('pacote.nome', 'Pacote Tradicional'));

        $this->post('/e/empresa-a/pacotes/pacote-tradicional/solicitacoes', [
            'nome_contato' => 'Cliente Teste', 'email' => 'cliente@example.com', 'telefone' => '11999999999',
            'data_evento' => '2030-01-10', 'convidados' => 50, 'tipo_local' => 'interno', 'aceita_contato' => true, 'item_ids' => [$item->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('solicitacoes_orcamento', ['empresa_id' => $empresa->id, 'pacote_id' => $pacote->id, 'preco_estimado' => 3750]);
        $this->assertDatabaseHas('solicitacao_itens', ['item_id' => $item->id, 'nome_snapshot' => 'Risoto']);

        $protocolo = SolicitacaoOrcamento::query()->value('protocolo');
        $this->get("/solicitacoes/confirmacao/{$protocolo}")
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page->component('Publico/Cardapio/Confirmacao')
                ->where('relatorio.preco_estimado', '3750.00')
                ->where('relatorio.aceita_contato', true)
                ->where('relatorio.itens.0.nome', 'Risoto'));

        $this->actingAs($user)->get('/app/solicitacoes')
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page->component('App/Solicitacoes/Index')->where('solicitacoes.0.protocolo', fn ($protocolo) => is_string($protocolo)));

        $solicitacao = SolicitacaoOrcamento::where('nome_contato', 'Cliente Teste')->firstOrFail();
        $this->actingAs($user)->patch("/app/solicitacoes/{$solicitacao->id}/proposta", [
            'preco_proposto' => 3600, 'desconto_proposto' => 150, 'validade_proposta' => '2030-01-20', 'observacoes_proposta' => 'Pagamento em duas parcelas.',
        ])->assertRedirect();
        $solicitacao->refresh();
        $this->assertNotNull($solicitacao->proposta_token);
        $this->get("/propostas/{$solicitacao->proposta_token}")
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page->component('Publico/Proposta')->where('proposta.cliente', 'Cliente Teste')->where('proposta.preco_proposto', '3600.00'));

        $this->post('/e/empresa-a/pacotes/pacote-tradicional/solicitacoes', [
            'nome_contato' => 'Cliente Personalizado', 'email' => 'personalizado@example.com', 'telefone' => '11988888888',
            'data_evento' => '2030-02-10', 'convidados' => 50, 'tipo_local' => 'interno', 'aceita_contato' => true,
            'modo_cardapio' => 'personalizado', 'item_ids' => [$item->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('solicitacoes_orcamento', [
            'empresa_id' => $empresa->id, 'modo_cardapio' => 'personalizado', 'preco_estimado' => 0,
        ]);
    }

    public function test_custom_menu_sums_beverage_quantity_by_unit_price(): void
    {
        [, $empresa] = $this->membership('drinks@example.com', 'Empresa A', 'empresa-a');
        $pacote = Pacote::create(['empresa_id' => $empresa->id, 'nome' => 'Base', 'slug' => 'base', 'tipo_preco' => 'por_pessoa', 'preco_base' => 75, 'min_convidados' => 30, 'publicado' => true, 'ativo' => true]);
        $categoria = CategoriaItem::create(['empresa_id' => $empresa->id, 'nome' => 'Bebidas', 'slug' => 'bebidas']);
        $bebida = ItemCatalogo::create(['empresa_id' => $empresa->id, 'categoria_id' => $categoria->id, 'nome' => 'Refrigerante', 'unidade' => 'lata', 'tipo_item' => 'bebida', 'preco_unitario' => 5]);

        $this->post('/e/empresa-a/cardapio-personalizado/solicitacoes', [
            'nome_contato' => 'Cliente Bebidas', 'email' => 'bebidas@example.com', 'telefone' => '11999999999',
            'data_evento' => '2030-03-10', 'convidados' => 30, 'tipo_local' => 'interno', 'aceita_contato' => true,
            'item_ids' => [$bebida->id], 'item_quantidades' => [$bebida->id => 12],
        ])->assertRedirect();

        $solicitacao = SolicitacaoOrcamento::latest('id')->firstOrFail();
        $this->assertSame('60.00', $solicitacao->preco_estimado);
        $this->assertDatabaseHas('solicitacao_itens', ['solicitacao_id' => $solicitacao->id, 'item_id' => $bebida->id, 'quantidade' => 12, 'subtotal_snapshot' => 60]);
    }

    private function membership(string $email, string $name, string $slug): array
    {
        $user = User::factory()->create(['email' => $email]);
        $empresa = Empresa::create(['nome_fantasia' => $name, 'slug' => $slug, 'email' => $email]);
        $empresa->usuarios()->attach($user, ['papel' => 'admin', 'ativo' => true]);

        return [$user, $empresa];
    }
}
