<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreIntakeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pathway' => ['required', 'in:explore,student-university,career-transition,employment-employability,professional-growth,advanced-technical,leadership-management,entrepreneurship,life-professional-success,community-cyber-safety'],
            'primary_cluster_id' => [
                'required',
                Rule::exists('clusters', 'id')->where('is_active', true),
            ],
            'secondary_cluster_ids' => ['nullable', 'array', 'max:3'],
            'secondary_cluster_ids.*' => ['integer', 'distinct', Rule::exists('clusters', 'id')->where('is_active', true)],
            'goals' => ['required', 'string', 'min:30', 'max:3000'],
            'experience_summary' => ['required', 'string', 'max:3000'],
            'preferred_language' => ['required', 'string', 'max:80'],
            'preferred_format' => ['required', 'in:virtual,in-person,hybrid'],
            'availability' => ['required', 'array', 'min:1'],
            'availability.*' => ['in:weekday-morning,weekday-afternoon,weekday-evening,weekend'],
            'mentoring_styles' => ['nullable', 'array', 'max:3'],
            'mentoring_styles.*' => ['string', Rule::in(array_keys(config('mentoring.styles')))],
            'mentoring_style' => ['nullable', 'string', 'max:500'],
            'university_context_status' => ['required', 'in:not-applicable,applies'],
            'university_context' => ['nullable', 'string', 'max:500', 'required_if:university_context_status,applies'],
            'accessibility_needs' => ['nullable', 'string', 'max:2000'],
            'conflict_status' => ['required', 'in:none,declare'],
            'conflict_declarations' => ['nullable', 'string', 'max:2000', 'required_if:conflict_status,declare'],
        ];
    }

    /**
     * Prepare backward-compatible defaults for clients using the earlier intake form.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'secondary_cluster_ids' => $this->input('secondary_cluster_ids', []),
            'mentoring_styles' => $this->input('mentoring_styles', []),
            'university_context_status' => $this->input('university_context_status', $this->filled('university_context') ? 'applies' : 'not-applicable'),
            'conflict_status' => $this->input('conflict_status', $this->filled('conflict_declarations') ? 'declare' : 'none'),
        ]);
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (in_array((int) $this->input('primary_cluster_id'), array_map('intval', $this->input('secondary_cluster_ids', [])), true)) {
                    $validator->errors()->add('secondary_cluster_ids', 'Your primary cluster cannot also be a secondary community.');
                }

                $styles = $this->input('mentoring_styles', []);
                if (in_array('no-preference', $styles, true) && count($styles) > 1) {
                    $validator->errors()->add('mentoring_styles', 'Choose “No strong preference” on its own.');
                }
            },
        ];
    }
}
