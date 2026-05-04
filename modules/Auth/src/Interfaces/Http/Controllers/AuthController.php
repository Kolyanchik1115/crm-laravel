<?php

declare(strict_types=1);

namespace Modules\Auth\src\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;
use Modules\Auth\src\Interfaces\Http\Requests\V1\LoginRequest;
use Modules\Auth\src\Interfaces\Http\Requests\V1\RegisterRequest;
use Modules\Auth\src\Application\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService,
    ) {
    }

    public function showLoginForm(): View
    {
        return view('auth::auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return back()->withErrors([
                'email' => 'Invalid credentials',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function showRegisterForm(): View
    {
        return view('auth::auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $dto = $request->toRegisterDTO();
        $result = $this->authService->register($dto);

        if (!$result) {
            return back()->withErrors([
                'email' => 'Registration failed. Please try again.',
            ])->withInput();
        }

        session(['jwt_token' => $result['access_token']]);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
