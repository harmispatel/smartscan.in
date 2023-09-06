<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FoodJunctionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name' => 'required',
            'shops' => 'required',
            'junction_logo' => 'mimes:png,jpg,svg,jpeg,PNG,SVG,JPG,JPEG',
        ];

        if(!$this->junction_id)
        {
            $rules += [
                'junction_slug' => 'required|regex:/^[a-zA-Z-]+$/|unique:food_junctions,junction_slug',
            ];
        }

        return $rules;
    }
}
