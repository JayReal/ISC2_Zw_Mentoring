<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStaffAccessRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $targetUser = $this->route('user');

        return $targetUser instanceof User
            && $this->user()?->hasAnyRole(['admin'])
            && ! $this->user()->is($targetUser);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'roles' => ['present', 'array'],
            'roles.*' => ['string', Rule::in(User::programmeRoles())],
        ];
    }

    /**
     * Prepare unchecked role controls for validation.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->has('roles')) {
            $this->merge(['roles' => []]);
        }
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.current_password' => 'Enter your current password to confirm this access change.',
            'roles.array' => 'Select programme roles using the available options.',
            'roles.*.in' => 'One or more selected programme roles are not recognised.',
        ];
    }
}
