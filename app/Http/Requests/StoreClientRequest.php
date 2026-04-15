<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    // add access later

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'unique:clients,email'],
            'balance' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'in:UAH,USD,EUR'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'ПІБ обов\'язкове поле',
            'full_name.min' => 'ПІБ має містити мінімум 2 символи',
            'full_name.max' => 'ПІБ не може перевищувати 255 символів',
            'email.required' => 'Email обов\'язкове поле',
            'email.email' => 'Введіть коректний email',
            'email.unique' => 'Цей email вже зареєстрований',
            'balance.numeric' => 'Баланс має бути числом',
            'balance.min' => 'Баланс не може бути від\'ємним',
            'currency.in' => 'Валюта має бути UAH, USD або EUR',
            'is_active.boolean' => 'Поле статусу має бути так/ні',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'balance' => $this->balance ?? 0,
            'currency' => $this->currency ?? 'UAH',
            'is_active' => $this->is_active ?? true,
        ]);
    }
}
