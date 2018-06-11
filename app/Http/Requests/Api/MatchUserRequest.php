<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Dingo\Api\Exception\ResourceException;
use Illuminate\Foundation\Http\FormRequest;

class MatchUserRequest extends FormRequest
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
                    'user2_id' => 'required|string',
                ];
                break;
            case 'DELETE':
                return [
                ];
                break;
        }
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ResourceException('表单验证不通过', $validator->errors());
    }
}
