<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\DTO\TransferDTO;
use App\ValueObjects\Money;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_from_id' => ['required', 'integer', 'exists:accounts,id'],
            'account_to_id' => ['required', 'integer', 'exists:accounts,id', 'different:account_from_id'],
            'amount' => ['required', 'string', 'regex:/^\d+(\.\d{1,2})?$/'],
            'currency' => ['required', 'string', 'in:UAH,USD,EUR'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'account_from_id.required' => 'Рахунок відправника є обов\'язковим',
            'account_from_id.integer' => 'ID рахунку відправника має бути цілим числом',
            'account_from_id.exists' => 'Рахунок відправника не знайдено',

            'account_to_id.required' => 'Рахунок отримувача є обов\'язковим',
            'account_to_id.integer' => 'ID рахунку отримувача має бути цілим числом',
            'account_to_id.exists' => 'Рахунок отримувача не знайдено',
            'account_to_id.different' => 'Рахунки мають бути різними',

            'amount.required' => 'Сума переказу є обов\'язковою',
            'amount.string' => 'Сума має бути текстовим рядком',
            'amount.regex' => 'Сума має бути додатнім числом з максимум двома знаками після коми',

            'currency.required' => 'Валюта є обов\'язковою',
            'currency.string' => 'Валюта має бути текстовим рядком',
            'currency.in' => 'Валюта має бути однією з: UAH, USD, EUR',

            'description.string' => 'Опис має бути текстовим рядком',
            'description.max' => 'Опис не може перевищувати 500 символів',
        ];
    }

    public function toDto(): TransferDTO
    {
        $validated = $this->validated();

        return new TransferDTO(
            accountFromId: (int) $validated['account_from_id'],
            accountToId: (int) $validated['account_to_id'],
            amount: new Money((string) $validated['amount'], (string) $validated['currency']),
            description: (string) ($validated['description'] ?? '')
        );
    }

    public function toTransferDTO(): TransferDTO
    {
        return $this->toDto();
    }
}
