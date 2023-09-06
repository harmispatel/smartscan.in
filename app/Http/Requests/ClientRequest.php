<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
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
            'firstname' => 'required',
            'subscription' => 'required',
            'shop_name' => 'required',
        ];


        if($this->client_id)
        {
            $rules += [
                'email' => 'required|email|unique:users,email,'.$this->client_id,
                // 'shop_url' => 'required|regex:/^[a-zA-Z]+$/|unique:shops,shop_slug,'.$this->shop_id,
                'confirm_password' => 'same:password',
            ];
        }
        else
        {
            $rules += [
                'email' => 'required|email|unique:users,email',
                'shop_url' => 'required|regex:/^[a-zA-Z-]+$/|unique:shops,shop_slug',
                'password' => 'required|min:6',
                'confirm_password' => 'required|same:password',
                'primary_language' => 'required',
            ];
        }

        return $rules;
    }
}
