<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\DTO\TransferDTO;
use App\ValueObjects\Money;
use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_id' => 'required|exists:accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|size:3|in:UAH,USD,EUR',
            'description' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'from_account_id.required' => 'Рахунок відправника обов\'язковий',
            'from_account_id.exists' => 'Рахунок відправника не існує',
            'to_account_id.required' => 'Рахунок отримувача обов\'язковий',
            'to_account_id.exists' => 'Рахунок отримувача не існує',
            'to_account_id.different' => 'Рахунки мають бути різними',
            'amount.required' => 'Сума переказу обов\'язкова',
            'amount.numeric' => 'Сума має бути числом',
            'amount.min' => 'Сума має бути не менше 0.01',
            'description.max' => 'Опис не може перевищувати 500 символів',
        ];
    }

    public function toTransferDTO(): TransferDTO
    {
        $validated = $this->validated();

        $currency = $validated['currency'] ?? 'UAH';

        return new TransferDTO(
            accountFromId: (int)$validated['from_account_id'],
            accountToId: (int)$validated['to_account_id'],
            amount: new Money(
                amount: (string)$validated['amount'],
                currency: $currency,
            ),
            description: (string)($validated['description'] ?? ''),
        );
    }
}
