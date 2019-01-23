<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Dingo\Api\Exception\ResourceException;

class TargetInfoRequest extends FormRequest
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
                    'sex' => 'required|string|in:女,男,不介意',
                    'hometown' => 'required|string',
                    'area' => 'required|string|in:北区,南区,不介意',
                    'school_place' => 'required|string',
                    'school_name' => 'required|string',
                    'school_field' => 'required|string',
                    'school_type' => 'required|string|in:学硕,专硕',
                    'study_style' => 'required|string|in:单独,团体',
                    'good_subject' => 'required|string',
                ];
                break;
        }
    }

    /**
     * 获取已定义的验证规则的错误消息。
     *
     * @return array
     */
    public function messages()
    {
        return [

        ];
    }

    protected function failedValidation(Validator $validator)
    {
//        throw new ResourceException('表单验证不通过', $validator->errors(),null,[],200);
        throw new ResourceException('表单验证不通过', $validator->errors());
    }
}
