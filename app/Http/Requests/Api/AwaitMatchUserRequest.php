<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Dingo\Api\Exception\ResourceException;
use Illuminate\Foundation\Http\FormRequest;

class AwaitMatchUserRequest extends FormRequest
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
                    'share_url' => 'string',
                ];
                break;
            case 'GET':
                return [
                    'user2_id' => 'required|string',
                    'share_url' => 'string',
                ];
                break;
            case 'DELETE':
                return [
                    'user2_id' => 'required|string',
                ];
                break;
        }
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ResourceException('表单验证不通过', $validator->errors());
    }
}
