<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExperienceRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100', // Maps to location column
            'state' => 'nullable|string|max:100', // Maps to county column
            'country' => 'required|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'nullable|in:draft,published,inactive',
            'images' => 'nullable|array',
            'images.*' => 'string|url',
            'amenities' => 'nullable|array',
            'amenities.*' => 'integer|exists:amenities,id',
            
            // Experience-specific fields
            'duration' => 'required|string|max:100',
            'activity_type' => 'required|in:outdoor,indoor,cultural,adventure,food_drink,wellness,educational,entertainment',
            'group_size' => 'required|integer|min:1|max:50',
            'difficulty_level' => 'required|in:easy,moderate,challenging,expert',
            'included_items' => 'nullable|array',
            'included_items.*' => 'string|max:255',
            'requirements' => 'nullable|string|max:2000',
            'cancellation_policy' => 'nullable|in:flexible,moderate,strict'
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Experience title is required.',
            'description.required' => 'Experience description is required.',
            'price.required' => 'Experience price is required.',
            'price.numeric' => 'Price must be a valid number.',
            'price.min' => 'Price cannot be negative.',
            'address.required' => 'Experience address is required.',
            'city.required' => 'City is required.',
            'country.required' => 'Country is required.',
            'duration.required' => 'Experience duration is required.',
            'activity_type.required' => 'Activity type is required.',
            'group_size.required' => 'Group size is required.',
            'group_size.min' => 'Group size must be at least 1.',
            'group_size.max' => 'Group size cannot exceed 100.',
            'difficulty_level.in' => 'Difficulty level must be one of: beginner, intermediate, advanced, expert.',
            'cancellation_policy.in' => 'Cancellation policy must be one of: flexible, moderate, strict.'
        ];
    }
}