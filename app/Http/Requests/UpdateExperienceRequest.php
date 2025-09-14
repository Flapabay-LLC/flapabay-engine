<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExperienceRequest extends FormRequest
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
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:5000',
            'price' => 'sometimes|numeric|min:0',
            'currency' => 'sometimes|string|size:3',
            'address' => 'sometimes|string|max:500',
            'city' => 'sometimes|required|string|max:100', // Maps to location column
            'state' => 'sometimes|nullable|string|max:100', // Maps to county column
            'country' => 'sometimes|string|max:100',
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
            'status' => 'sometimes|in:draft,published,inactive',
            'images' => 'sometimes|array',
            'images.*' => 'string|url',
            'amenities' => 'sometimes|array',
            'amenities.*' => 'integer|exists:amenities,id',
            
            // Experience-specific fields
            'duration' => 'sometimes|string|max:100',
            'activity_type' => 'sometimes|in:outdoor,indoor,cultural,adventure,food_drink,wellness,educational,entertainment',
            'group_size' => 'sometimes|integer|min:1|max:50',
            'difficulty_level' => 'sometimes|in:easy,moderate,challenging,expert',
            'included_items' => 'sometimes|array',
            'included_items.*' => 'string|max:255',
            'requirements' => 'sometimes|string|max:2000',
            'cancellation_policy' => 'sometimes|in:flexible,moderate,strict'
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'title.string' => 'Experience title must be a string.',
            'title.max' => 'Experience title cannot exceed 255 characters.',
            'description.string' => 'Experience description must be a string.',
            'description.max' => 'Experience description cannot exceed 5000 characters.',
            'price.numeric' => 'Price must be a valid number.',
            'price.min' => 'Price cannot be negative.',
            'currency.size' => 'Currency must be a 3-character code.',
            'address.string' => 'Address must be a string.',
            'address.max' => 'Address cannot exceed 500 characters.',
            'city.string' => 'City must be a string.',
            'city.max' => 'City cannot exceed 100 characters.',
            'state.string' => 'State must be a string.',
            'state.max' => 'State cannot exceed 100 characters.',
            'country.string' => 'Country must be a string.',
            'country.max' => 'Country cannot exceed 100 characters.',
            'latitude.numeric' => 'Latitude must be a valid number.',
            'latitude.between' => 'Latitude must be between -90 and 90.',
            'longitude.numeric' => 'Longitude must be a valid number.',
            'longitude.between' => 'Longitude must be between -180 and 180.',
            'status.in' => 'Status must be one of: draft, published, inactive.',
            'images.array' => 'Images must be an array.',
            'images.*.url' => 'Each image must be a valid URL.',
            'amenities.array' => 'Amenities must be an array.',
            'amenities.*.exists' => 'Selected amenity does not exist.',
            'duration.string' => 'Duration must be a string.',
            'duration.max' => 'Duration cannot exceed 100 characters.',
            'activity_type.string' => 'Activity type must be a string.',
            'activity_type.max' => 'Activity type cannot exceed 100 characters.',
            'group_size.integer' => 'Group size must be an integer.',
            'group_size.min' => 'Group size must be at least 1.',
            'group_size.max' => 'Group size cannot exceed 100.',
            'difficulty_level.in' => 'Difficulty level must be one of: beginner, intermediate, advanced, expert.',
            'included_items.array' => 'Included items must be an array.',
            'included_items.*.string' => 'Each included item must be a string.',
            'included_items.*.max' => 'Each included item cannot exceed 255 characters.',
            'requirements.string' => 'Requirements must be a string.',
            'requirements.max' => 'Requirements cannot exceed 2000 characters.',
            'cancellation_policy.in' => 'Cancellation policy must be one of: flexible, moderate, strict.'
        ];
    }
}