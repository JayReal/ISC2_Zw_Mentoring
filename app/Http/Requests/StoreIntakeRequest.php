<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
            'primary_cluster_id' => ['required', 'exists:clusters,id'],
            'goals' => ['required', 'string', 'min:30', 'max:3000'],
            'experience_summary' => ['required', 'string', 'max:3000'],
            'preferred_language' => ['required', 'string', 'max:80'],
            'preferred_format' => ['required', 'in:virtual,in-person,hybrid'],
            'availability' => ['required', 'array', 'min:1'],
            'availability.*' => ['in:weekday-morning,weekday-afternoon,weekday-evening,weekend'],
            'mentoring_style' => ['nullable', 'string', 'max:500'],
            'university_context' => ['nullable', 'string', 'max:255'],
            'accessibility_needs' => ['nullable', 'string', 'max:2000'],
            'conflict_declarations' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
