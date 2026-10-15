<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmpresaIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_only_receives_data_from_an_empresa_where_they_are_active(): void
    {
        [$userA, $empresaA] = $this->createMembership('a@example.com', 'Empresa A', 'empresa-a');
        [, $empresaB] = $this->createMembership('b@example.com', 'Empresa B', 'empresa-b');

        $this->actingAs($userA)->withSession(['empresa_id' => $empresaB->id])
            ->get('/app/empresa')
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('App/Empresa/Show')
                ->where('empresa.nome_fantasia', 'Empresa A'));
    }

    public function test_user_cannot_switch_to_another_empresa(): void
    {
        [$userA] = $this->createMembership('a@example.com', 'Empresa A', 'empresa-a');
        [, $empresaB] = $this->createMembership('b@example.com', 'Empresa B', 'empresa-b');

        $this->actingAs($userA)
            ->post("/app/empresas/{$empresaB->id}/ativar")
            ->assertForbidden();
    }

    public function test_user_cannot_update_another_empresa_by_forcing_its_id(): void
    {
        [$userA, $empresaA] = $this->createMembership('a@example.com', 'Empresa A', 'empresa-a');
        [, $empresaB] = $this->createMembership('b@example.com', 'Empresa B', 'empresa-b');

        $this->actingAs($userA)->withSession(['empresa_id' => $empresaB->id])
            ->put('/app/empresa', [
                'nome_fantasia' => 'Atualização legítima da A',
                'slug' => 'empresa-a',
                'email' => 'a@example.com',
                'timezone' => 'America/Sao_Paulo',
            ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('empresas', ['id' => $empresaA->id, 'nome_fantasia' => 'Atualização legítima da A']);
        $this->assertDatabaseHas('empresas', ['id' => $empresaB->id, 'nome_fantasia' => 'Empresa B']);
    }

    private function createMembership(string $email, string $name, string $slug): array
    {
        $user = User::factory()->create(['email' => $email]);
        $empresa = Empresa::create(['nome_fantasia' => $name, 'slug' => $slug, 'email' => $email]);
        $empresa->usuarios()->attach($user, ['papel' => 'admin', 'ativo' => true]);

        return [$user, $empresa];
    }
}
