<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_a_company(): void
    {
        $response = $this->post('/cadastro', [
            'name' => 'Maria Administradora',
            'email' => 'maria@example.com',
            'empresa' => 'Buffet Exemplo',
            'password' => 'Senha-forte-123',
            'password_confirmation' => 'Senha-forte-123',
        ]);

        $response->assertRedirect('/app/dashboard');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('empresas', ['slug' => 'buffet-exemplo']);
        $this->assertDatabaseHas('empresa_user', ['papel' => 'owner', 'ativo' => true]);
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create(['password' => 'Senha-forte-123']);
        $empresa = Empresa::create([
            'nome_fantasia' => 'Buffet Exemplo', 'slug' => 'buffet-exemplo',
            'email' => 'buffet@example.com',
        ]);
        $empresa->usuarios()->attach($user, ['papel' => 'admin', 'ativo' => true]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'Senha-forte-123',
        ])->assertRedirect('/app/dashboard');

        $this->assertAuthenticatedAs($user);
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }
}
