<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePropertyRequest extends FormRequest
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
            'title' => 'nullable',
            'description' => 'nullable',
            'location' => 'nullable',
            'address' => 'nullable',
            'county' => 'nullable',
            'latitude' => 'nullable',
            'longitude' => 'nullable',
            'check_in_hour'  => 'nullable',
            'check_out_hour' => 'nullable',
            'num_of_guests' => 'nullable',
            'num_of_children' => 'nullable',
            'maximum_guests' => 'nullable',
            'country' => 'nullable',
            'currency' => 'nullable',
            'price_range' => 'nullable',
            'price' => 'nullable',
            'additional_guest_price' => 'nullable',
            'children_price' => 'nullable',
            'amenities' => 'nullable',
            'house_rules' => 'nullable',
            'page' => 'nullable',
            'rating' => 'nullable',
            'favorite' => 'nullable',
            'images' => 'nullable|array',
            'video_link' => 'nullable',
            'verified' => 'nullable',
            'property_type_id' => 'nullable',
            'category_id' => 'required',
            'tags' => 'nullable',
        ];
    }
}
