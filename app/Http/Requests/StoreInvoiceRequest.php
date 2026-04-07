<?php

declare(strict_types=1);

namespace App\Http\Requests;

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
}
