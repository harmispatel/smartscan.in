<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TutorialRequest extends FormRequest
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
            'title' => 'required',
            'text' => 'required',
        ];


        if(empty($this->tutorial_id) || $this->tutorial_id == null)
        {
            $rules += [
                'file' => 'required',
                
            ];
        }
        return $rules;
    }
}
