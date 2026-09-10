<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $actor = $this->user();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')],
            'password' => ['required', 'string', 'min:8'],
            'client_id' => $actor->isSuperAdmin()
                ? ['nullable', Rule::exists('clients', 'id')]
                : ['required', Rule::in([$actor->client_id])],
            'role_id' => $actor->isSuperAdmin()
                ? ['nullable', Rule::exists('roles', 'id')]
                : ['nullable', Rule::exists('roles', 'id')->where(fn ($q) => $q->where('slug', '!=', 'super-admin'))],
        ];
    }
}
