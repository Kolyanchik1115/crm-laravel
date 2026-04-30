<?php

declare(strict_types=1);

namespace Modules\Auth\src\Interfaces\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Auth\src\Application\DTO\LoginDTO;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email is required',
            'email.email' => 'Invalid email format',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 6 characters',
        ];
    }

    public function toLoginDTO(): LoginDTO
    {
        return LoginDTO::fromRequest($this->validated());
    }
}
