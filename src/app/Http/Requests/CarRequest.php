<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CarRequest extends FormRequest
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
        // TODO: Ideally we shuld have tables for brands and models to validate against them as well
        return [
            'brand' => 'required|string|min:2|max:100',
            'model' => 'required|string|min:1|max:100',
            'year' => 'required|integer|min:1886|max:' . date('Y'),
            'price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'options' => 'nullable|array',
            'options.*' => 'string|max:50',
            'photos' => 'nullable|array|max:10',
            'photos.*' => 'image|max:5120',
            'primary_photo_index' => 'nullable|integer|min:0',
        ];
    }
}
