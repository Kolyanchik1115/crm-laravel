<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:draft,pending,paid,cancelled'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Статус обов\'язковий',
            'status.in' => 'Статус має бути: draft, pending, paid, cancelled',
        ];
    }
}
