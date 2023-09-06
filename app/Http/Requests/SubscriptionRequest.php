<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscriptionRequest extends FormRequest
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
            'price' => 'required|numeric',
            'duration' => 'required',
            'icon' => 'mimes:png,jpg,svg,jpeg,PNG,SVG,JPG,JPEG|dimensions:width=200,height=200',
        ];

        if($this->subscription_id)
        {
            $rules += [
                'title' => 'required|unique:subscriptions,name,'.$this->subscription_id,
            ];
        }
        else
        {
            $rules += [
                'title' => 'required|unique:subscriptions,name',
            ];
        }

        return $rules;
    }
}
