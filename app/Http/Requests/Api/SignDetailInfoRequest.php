<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Dingo\Api\Exception\ResourceException;

class SignDetailInfoRequest extends FormRequest
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

    public function rules()
    {
        switch ($this->method()) {
            case 'POST':
                return [
                ];
            case 'PATCH':
                return [
                ];
        }
    }

    protected function failedValidation(Validator $validator)
    {
//        throw new ResourceException('表单验证不通过', $validator->errors(),null,[],200);
        throw new ResourceException('表单验证不通过', $validator->errors());
    }
}
