<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminSessionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $allowedCredentials = [
            config('app.admin_login') => config('app.admin_password'),
            config('app.super_admin_login') => config('app.super_admin_password'),
        ];

        $expectedPassword = $allowedCredentials[$credentials['email']] ?? null;

        if (! $expectedPassword || $expectedPassword !== $credentials['password']) {
            return back()->withErrors(['email' => 'Nieprawidłowe dane logowania.'])->onlyInput('email');
        }

        $user = User::query()->where('email', $credentials['email'])->first();
        if (! $user) {
            return back()->withErrors(['email' => 'Użytkownik nie istnieje. Uruchom komendę admin:sync.'])->onlyInput('email');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
