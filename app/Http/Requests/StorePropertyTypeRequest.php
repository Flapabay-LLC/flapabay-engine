<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePropertyTypeRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:property_types,name',
            'black_icon' => 'required|string|max:255',
            'white_icon' => 'required|string|max:255',
            'description' => 'required|string',
            'bg_color' => 'required|string|max:7|regex:/^#[0-9A-F]{6}$/i',
            'color' => 'required|string|max:7|regex:/^#[0-9A-F]{6}$/i',
            'type' => 'required|string|max:50'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The property type name is required.',
            'name.unique' => 'This property type name already exists.',
            'black_icon.required' => 'The black icon is required.',
            'white_icon.required' => 'The white icon is required.',
            'description.required' => 'The description is required.',
            'bg_color.required' => 'The background color is required.',
            'bg_color.regex' => 'The background color must be a valid hex color code.',
            'color.required' => 'The text color is required.',
            'color.regex' => 'The text color must be a valid hex color code.',
            'type.required' => 'The property type category is required.'
        ];
    }
}
