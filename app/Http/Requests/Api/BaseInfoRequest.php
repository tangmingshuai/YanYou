<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Dingo\Api\Exception\ResourceException;

class BaseInfoRequest extends FormRequest
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
                    'name' => 'string|max:10',
                    'phone' => 'numeric|unique:user_base_infos,phone',
                    'sex' => 'string|in:女,男',
                    'hometown' => 'string',
                    'area' => 'string|in:北区,南区',
                    'school_place' => 'string',
                    'school_name' => 'string',
                    'school_field' => 'string',
                    'school_type' => 'string|in:学硕,专硕',
                    'study_style' =>'string|in:单独,团体',
                    'good_subject' => 'string',
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
