<?php

declare(strict_types=1);

namespace Modules\Invoice\src\Interfaces\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Invoice\src\Application\DTO\CreateInvoiceDTO;
use Modules\Invoice\src\Application\DTO\InvoiceItemDTO;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'currency' => ['nullable', 'string', 'in:UAH,USD,EUR'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.service_id' => ['required', 'integer', 'exists:services,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'string', 'regex:/^\d+(\.\d{1,2})?$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'Клієнт є обов\'язковим',
            'client_id.integer' => 'ID клієнта має бути цілим числом',
            'client_id.exists' => 'Клієнт не знайдений',

            'currency.in' => 'Валюта має бути однією з: UAH, USD, EUR',

            'items.required' => 'Потрібно додати хоча б одну позицію',
            'items.array' => 'Позиції мають бути масивом',
            'items.min' => 'Мінімум одна позиція в рахунку',

            'items.*.service_id.required' => 'ID послуги є обов\'язковим',
            'items.*.service_id.integer' => 'ID послуги має бути цілим числом',
            'items.*.service_id.exists' => 'Послугу не знайдено',

            'items.*.quantity.required' => 'Кількість є обов\'язковою',
            'items.*.quantity.integer' => 'Кількість має бути цілим числом',
            'items.*.quantity.min' => 'Кількість має бути не менше 1',

            'items.*.unit_price.required' => 'Ціна є обов\'язковою',
            'items.*.unit_price.string' => 'Ціна має бути текстовим рядком',
            'items.*.unit_price.regex' => 'Ціна має бути додатнім числом з максимум двома знаками після коми',
        ];
    }

    public function toCreateInvoiceDTO(): CreateInvoiceDTO
    {
        $validated = $this->validated();

        $items = array_map(
            fn (array $item) => new InvoiceItemDTO(
                serviceId: (int) $item['service_id'],
                quantity: (int) $item['quantity'],
                unitPrice: (float) $item['unit_price'],
            ),
            $validated['items']
        );

        return new CreateInvoiceDTO(
            clientId: (int) $validated['client_id'],
            items: $items,
            currency: $validated['currency'] ?? 'UAH',
        );
    }
}
