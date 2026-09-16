<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePricingTierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // gated by the 'internal-admin' route middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'min_credits' => ['required', 'integer', 'min:1'],
            'price_per_credit' => ['required', 'numeric', 'min:0.0001'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
