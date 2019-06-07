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
                    'name' => 'string',
                    'phone' => 'numeric|unique:user_base_infos,phone|regex:/^1[345789][0-9]{9}$/',
                    'sex' => 'string|in:女,男',
                    'hometown' => 'string',
                    'area' => 'string|in:北区,南区',
                    'school_place' => 'string',
                    'school_name' => 'string',
                    'school_field' => 'string',
                    'school_type' => 'string|in:学硕,专硕,不确定',
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
            'phone.phone' => '手机号填写有误',
            'phone.regex' => '手机号填写有误',
            'phone.numeric' => '手机号填写有误',
            'name.string'  => '信息填写不完整',
            'sex.string'  => '信息填写不完整',
            'hometown.string'  => '信息填写不完整',
            'area.string'  => '信息填写不完整',
            'school_place.string'  => '信息填写不完整',
            'school_name.string'  => '信息填写不完整',
            'school_field.string'  => '信息填写不完整',
            'school_type.string'  => '信息填写不完整',
            'study_style.string'  => '信息填写不完整',
            'good_subject.string'  => '信息填写不完整',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
//        throw new ResourceException('表单验证不通过', $validator->errors(),null,[],200);
        throw new ResourceException('表单验证不通过', $validator->errors());
    }
}
