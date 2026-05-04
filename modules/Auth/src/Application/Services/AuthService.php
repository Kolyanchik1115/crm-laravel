<?php

declare(strict_types=1);

namespace Modules\Auth\src\Application\Services;

use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\JWTAuth;
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
        protected JWTAuth $jwt
    ) {
    }

    public function login(LoginDTO $dto): ?array
    {
        $credentials = $dto->toArray();
        $token = $this->jwt->attempt($credentials);
        if (!$token) {
            return null;
        }
        $user = $this->userRepository->findByEmail($dto->email);
        $user?->load('roles');
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl', 60) * 60,
            'user' => $user
        ];
    }

    public function register(RegisterDTO $dto): ?array
    {
        $existingUser = $this->userRepository->findByEmail($dto->email);
        if ($existingUser) {
            return null;
        }
        $user = $this->userRepository->create($dto->toArray());
        $defaultRole = $this->roleRepository->getDefaultUserRole();
        if ($defaultRole) {
            $this->roleRepository->assignRoleToUser($user->id, $defaultRole->id);
        }
        return $this->login(new LoginDTO($dto->email, $dto->password));
    }

    public function me(): ?User
    {
        $authUser = Auth::user();
        if (!$authUser instanceof User) {
            return null;
        }
        return $this->userRepository->findById($authUser->id);
    }

    public function logout(): void
    {
        $token = $this->jwt->parseToken()->invalidate();
    }

    public function refresh(): RefreshTokenDTO
    {
        $token = $this->jwt->parseToken()->refresh();

        return RefreshTokenDTO::fromToken($token);
    }
}
