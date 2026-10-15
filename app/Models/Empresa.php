<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Empresa extends Model
{
    use HasFactory;

    protected $table = 'empresas';

    protected $fillable = [
        'nome_fantasia', 'razao_social', 'documento', 'slug', 'email',
        'telefone', 'logo_path', 'timezone', 'ativo',
    ];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['papel', 'ativo'])
            ->withTimestamps();
    }

    public function usuarioAtivo(User $user): bool
    {
        return $this->usuarios()
            ->whereKey($user->getKey())
            ->wherePivot('ativo', true)
            ->exists();
    }
}
