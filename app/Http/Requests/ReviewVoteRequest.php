<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewVoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'choices' => ['required', 'array'],
            'choices.*' => ['required', 'integer'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'choices.required' => 'Sélectionnez au moins un candidat.',
            'choices.*.required' => 'Sélectionnez au moins un candidat.',
        ];
    }
}
