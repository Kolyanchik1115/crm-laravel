<?php

declare(strict_types=1);

namespace Modules\Auth\src\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Auth\src\Application\Services\AuthService;
use Modules\Auth\src\Interfaces\Http\Requests\V1\LoginRequest;
use Modules\Auth\src\Interfaces\Http\Requests\V1\RegisterRequest;

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
        $dto = $request->toLoginDTO();
        $result = $this->authService->login($dto);

        if (!$result) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        session(['jwt_token' => $result['access_token']]);

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

    public function logout(): RedirectResponse
    {
        $this->authService->logout();
        session()->forget('jwt_token');
        session()->flush();

        return redirect()->route('login');
    }
}
