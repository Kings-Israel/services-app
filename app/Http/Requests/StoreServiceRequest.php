<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check() && auth()->user()->hasRole('vendor');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required',
            'price_min' => 'required',
            'location_lat' => 'required',
            'location_long' => 'required',
            'categories' => 'required|array',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Enter title',
            'price_min.required' => 'Enter minimum price for the service',
            'location_lat.required' => 'Enter location lat',
            'location_long.required' => 'Enter location long',
            'categories.required' => 'Please select a category',
        ];
    }
}
