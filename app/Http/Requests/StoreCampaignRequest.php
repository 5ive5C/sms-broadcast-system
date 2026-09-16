<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCampaignRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('campaigns.manage');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $clientId = $this->user()->actingClient()->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'template_id' => ['nullable', Rule::exists('message_templates', 'id')->where('client_id', $clientId)],
            'content' => ['required', 'string', 'max:1600'],
            'lane' => ['required', Rule::in(['transactional', 'bulk'])],
        ];
    }
}
