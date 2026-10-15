<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function createLogin()
    {
        return Inertia::render('Auth/Login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (! Auth::attempt(
            ['email' => $credentials['email'], 'password' => $credentials['password']],
            (bool) ($credentials['remember'] ?? false)
        )) {
            return back()->withErrors(['email' => 'As credenciais informadas são inválidas.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('app.dashboard'));
    }

    public function createRegister()
    {
        return Inertia::render('Auth/Register');
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data) {
            $baseSlug = Str::slug($data['empresa']);
            $slug = $baseSlug;
            $counter = 2;

            while (Empresa::where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$counter++;
            }

            $empresa = Empresa::create([
                'nome_fantasia' => $data['empresa'],
                'slug' => $slug,
                'email' => $data['email'],
            ]);

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            $empresa->usuarios()->attach($user, ['papel' => 'owner', 'ativo' => true]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('empresa_id', $user->empresas()->first()->id);

        return redirect()->route('app.dashboard');
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }
}
