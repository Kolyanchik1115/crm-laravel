<?php

declare(strict_types=1);

namespace Modules\Auth\src\Application\Services;

use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Modules\Auth\src\Application\DTO\LoginDTO;
use Modules\Auth\src\Application\DTO\RegisterDTO;
use Modules\Auth\src\Application\DTO\RefreshTokenDTO;
use Modules\Auth\src\Domain\Entities\User;
use Modules\Auth\src\Domain\Repositories\UserRepositoryInterface;
use Modules\Auth\src\Domain\Repositories\RoleRepositoryInterface;

class AuthService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected RoleRepositoryInterface $roleRepository,
    ) {
    }

    public function login(LoginDTO $dto): ?array
    {
        $credentials = $dto->toArray();

        if (!$token = JWTAuth::attempt($credentials)) {
            return null;
        }

        $user = $this->userRepository->findByEmail($dto->email);
        $user?->load('roles');

        return [
            'access_token' => $token,
            'expires_in' => config('jwt.ttl', 60) * 60,
            'user' => $user,
        ];
    }

    public function register(RegisterDTO $dto): ?array
    {
        // Check if user exists
        $existingUser = $this->userRepository->findByEmail($dto->email);
        if ($existingUser) {
            return null;
        }

        // Create user
        $user = $this->userRepository->create($dto->toArray());

        // Assign default role
        $defaultRole = $this->roleRepository->getDefaultUserRole();
        if ($defaultRole) {
            $this->roleRepository->assignRoleToUser($user->id, $defaultRole->id);
        }

        // Auto login after registration
        $loginDTO = new LoginDTO($dto->email, $dto->password);
        return $this->login($loginDTO);
    }

    public function me(): ?User
    {
        $user = Auth::user();
        if ($user) {
            return $this->userRepository->findById($user->id);
        }
        return null;
    }

    public function logout(): void
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    }

    public function refresh($token): RefreshTokenDTO
    {
        $newToken = JWTAuth::refresh($token);

        return RefreshTokenDTO::fromToken($newToken);
    }
}
