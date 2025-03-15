<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePreferencesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
            'sources' => 'array',
            'sources.*' => 'exists:sources,id',
            'authors' => 'array',
            'authors.*' => 'exists:authors,id',
        ];
    }
}
