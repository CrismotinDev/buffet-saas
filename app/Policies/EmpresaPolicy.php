<?php

namespace App\Policies;

use App\Models\Empresa;
use App\Models\User;

class EmpresaPolicy
{
    public function view(User $user, Empresa $empresa): bool
    {
        return $user->podeAcessarEmpresa($empresa);
    }

    public function update(User $user, Empresa $empresa): bool
    {
        if (! $user->podeAcessarEmpresa($empresa)) {
            return false;
        }

        return in_array($empresa->pivot?->papel, ['owner', 'admin'], true);
    }
}
