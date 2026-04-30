<?php

declare(strict_types=1);

namespace Modules\Auth\src\Interfaces\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Auth\src\Application\DTO\RegisterDTO;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required',
            'last_name.required' => 'Last name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Invalid email format',
            'email.unique' => 'Email already exists',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 6 characters',
            'password.confirmed' => 'Password confirmation does not match',
        ];
    }

    public function toRegisterDTO(): RegisterDTO
    {
        return RegisterDTO::fromRequest($this->validated());
    }
}
