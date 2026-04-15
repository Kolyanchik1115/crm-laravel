<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\DTO\CreateInvoiceDTO;
use App\DTO\InvoiceItemDTO;
use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'currency' => 'nullable|string|size:3|in:UAH,USD,EUR',
            'items' => 'required|array|min:1',
            'items.*.service_id' => 'required|exists:services,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'Клієнт обов\'язковий',
            'client_id.exists' => 'Клієнт не існує',
            'items.required' => 'Потрібно додати хоча б одну позицію',
            'items.*.service_id.required' => 'Послуга обов\'язкова',
            'items.*.service_id.exists' => 'Послуга не існує',
            'items.*.quantity.min' => 'Кількість має бути не менше 1',
            'items.*.unit_price.min' => 'Ціна не може бути від\'ємною',
        ];
    }

    public function toCreateInvoiceDTO(): CreateInvoiceDTO
    {
        $validated = $this->validated();

        $items = array_map(
            fn ($item) => new InvoiceItemDTO(
                serviceId: (int)$item['service_id'],
                quantity: (int)$item['quantity'],
                unitPrice: (float)$item['unit_price'],
            ),
            $validated['items']
        );

        return new CreateInvoiceDTO(
            clientId: (int)$validated['client_id'],
            items: $items,
            currency: $validated['currency'] ?? 'UAH',
        );
    }
}
