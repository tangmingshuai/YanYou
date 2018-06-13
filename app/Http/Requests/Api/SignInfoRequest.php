<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Dingo\Api\Exception\ResourceException;

class SignInfoRequest extends FormRequest
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
        switch ($this->method()) {
            case 'POST':
                return [
                    'sign_day' => 'required|string',
                    'sign_score' => 'required|string'
                ];
            case 'PATCH':
                return [
                    'sign_day' => 'required|string',
                    'sign_score' => 'required|string'
                ];
        }
    }

    protected function failedValidation(Validator $validator)
    {
//        throw new ResourceException('表单验证不通过', $validator->errors(),null,[],200);
        throw new ResourceException('表单验证不通过', $validator->errors());
    }
}
