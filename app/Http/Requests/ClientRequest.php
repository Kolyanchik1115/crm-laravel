<?php
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
{
    // add access later

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients',
            'balance' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3|in:UAH,USD,EUR',
        ];
    }
}
