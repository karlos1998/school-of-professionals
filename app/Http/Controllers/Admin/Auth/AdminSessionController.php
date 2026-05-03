<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminLoginRequest;
use App\Services\Admin\AdminAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminSessionController extends Controller
{
    public function __construct(public AdminAuthService $adminAuthService) {}

    public function store(AdminLoginRequest $request): RedirectResponse
    {
        /** @var array{email:string,password:string} $credentials */
        $credentials = $request->validated();

        $result = $this->adminAuthService->attempt(
            $credentials,
            (string) $request->ip(),
        );

        if (! $result->success || $result->user === null) {
            return back()
                ->withErrors([
                    'email' => $result->errorMessage ?? 'Błąd logowania.',
                ])
                ->onlyInput('email');
        }

        Auth::login($result->user);
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
