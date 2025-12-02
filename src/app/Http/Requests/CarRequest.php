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
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');
        
        $rules = [
            'brand' => ['string', 'min:2', 'max:100'],
            'model' => ['string', 'min:1', 'max:100'],
            'year' => ['integer', 'min:1886', 'max:' . date('Y')],
            'price' => ['numeric', 'min:0'],
            'description' => 'nullable|string|max:1000',
            'options' => 'sometimes|array',
            'options.*' => 'string|max:50|nullable',
            'photos' => 'nullable|array|max:10',
            'photos.*' => 'file|mimes:jpg,jpeg,png,bmp,gif,svg,webp,avif|max:5120',
            'primary_photo_index' => 'nullable|integer|min:0',
            'primary_photo_id' => 'nullable|integer|exists:car_photos,id',
            'removed_photo_ids' => 'nullable|array',
            'removed_photo_ids.*' => 'integer|distinct|exists:car_photos,id',
        ];

        $requiredFields = ['brand', 'model', 'year', 'price'];

        if ($isUpdate) {
            foreach ($requiredFields as $field) {
                array_unshift($rules[$field], 'sometimes', 'required');
            }
        } else {
            foreach ($requiredFields as $field) {
                array_unshift($rules[$field], 'required');
            }
        }

        return $rules;
    }
}
