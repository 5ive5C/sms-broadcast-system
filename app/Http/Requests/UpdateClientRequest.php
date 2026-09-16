<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('client'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'company_reg_no' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'industry' => ['nullable', 'string', 'max:255'],
            'pricing_tier_id' => ['nullable', 'exists:pricing_tiers,id'],
            'message_types' => ['nullable', 'array'],
            'message_types.*' => ['string', 'in:tac,transactional,bulk'],
            'two_factor_required' => ['nullable', 'boolean'],
            'pic_name' => ['required', 'string', 'max:255'],
            'pic_phone' => ['required', 'string', 'max:50'],
            'pic_email' => ['required', 'string', 'email', 'max:255'],
        ];
    }
}
