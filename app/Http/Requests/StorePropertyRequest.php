<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePropertyRequest extends FormRequest
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
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'county' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'check_in_hour' => 'required|string|max:10',
            'check_out_hour' => 'required|string|max:10',
            'num_of_guests' => 'required|integer',
            'num_of_children' => 'nullable|integer',
            'maximum_guests' => 'required|integer',
            // 'allow_extra_guests' => 'boolean',
            // 'neighborhood_area' => 'nullable|string|max:255',
            // 'show_contact_form_instead_of_booking' => 'boolean',
            // 'allow_instant_booking' => 'boolean',
            'country' => 'required|string|max:255',
            'currency' => 'required|string|max:10',
            'price_range' => 'required|string|max:50',
            'price' => 'required|numeric',
            // 'price_per_night' => 'required|numeric',
            'additional_guest_price' => 'nullable|numeric',
            'children_price' => 'nullable|numeric',
            'amenities' => 'nullable|string',
            'house_rules' => 'nullable|string',
            'page' => 'nullable|string|max:255',
            'rating' => 'nullable|numeric',
            'favorite' => 'boolean',
            'images' => 'nullable|array',
            'video_link' => 'nullable',
            'verified' => 'boolean',
            'property_type_id' => 'nullable',
            'category_id' => 'required',
            'tags' => 'nullable',
        ];
    }
    
}
