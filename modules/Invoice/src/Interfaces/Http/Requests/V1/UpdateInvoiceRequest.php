<?php

declare(strict_types=1);

namespace Modules\Invoice\src\Interfaces\Http\Requests\V1;

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
            'status' => 'required|string|in:draft,sent,paid,overdue,cancelled',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status is required',
            'status.in' => 'Status must be: draft, sent, paid, overdue, cancelled',
        ];
    }
}
