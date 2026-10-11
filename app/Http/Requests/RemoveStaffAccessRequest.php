<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RemoveStaffAccessRequest extends FormRequest
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
            'confirm_removal' => ['required', 'accepted'],
        ];
    }
}
