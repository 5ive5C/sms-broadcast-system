<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuickSendRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('quick-send.manage');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:1600'],
            'recipients_text' => ['nullable', 'string', 'required_without:recipients_file'],
            'recipients_file' => ['nullable', 'file', 'extensions:txt,csv', 'max:5120', 'required_without:recipients_text'],
        ];
    }
}
