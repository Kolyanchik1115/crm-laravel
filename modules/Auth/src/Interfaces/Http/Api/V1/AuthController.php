<?php

declare(strict_types=1);

namespace Modules\Auth\src\Interfaces\Http\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Auth\src\Application\Services\AuthService;
use Modules\Auth\src\Interfaces\Http\Resources\V1\AuthResource;
use Modules\Auth\src\Interfaces\Http\Resources\V1\RefreshTokenResource;
use Modules\Auth\src\Interfaces\Http\Resources\V1\UserResource;
use Modules\Auth\src\Interfaces\Http\Requests\V1\LoginRequest;
use Modules\Auth\src\Interfaces\Http\Requests\V1\RegisterRequest;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $dto = $request->toLoginDTO();
        $result = $this->authService->login($dto);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        return (new AuthResource($result))
            ->additional(['success' => true])
            ->response()
            ->setStatusCode(200);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $dto = $request->toRegisterDTO();
        $result = $this->authService->register($dto);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'User already exists',
            ], 422);
        }

        return (new AuthResource($result))
            ->additional(['success' => true, 'message' => 'Registration successful'])
            ->response()
            ->setStatusCode(201);
    }

    public function me(): JsonResponse
    {
        $user = $this->authService->me();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        return (new UserResource($user))
            ->additional(['success' => true])
            ->response()
            ->setStatusCode(200);
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh(): JsonResponse
    {
        //add get token method in controller due to error with finding token in service
        $token = JWTAuth::getToken();

        $refreshTokenDTO = $this->authService->refresh($token);

        return (new RefreshTokenResource($refreshTokenDTO))
            ->additional(['success' => true])
            ->response()
            ->setStatusCode(200);
    }
}
