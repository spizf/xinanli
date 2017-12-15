<?php
namespace App\Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RealnameAuthRequest extends FormRequest
{
	
	public function rules()
	{
        return [
            'realname' => 'required|string',
            'card_number' => 'required',
            'enterprise_nature' => 'required',
            'regist_time' => 'required',
            'validation_img' => 'required'
        ];
	}

	
	public function authorize()
	{
		return true;
	}

    public function messages()
    {
        return [
            'realname.required' => '请输入企业名称',
            'realname.string' => '请输入正确的格式',

            'card_number.required' => '请输入营业执照号',

            'enterprise_nature.required' => '请选择企业性质',
            'regist_time.required' => '请填写注册时间',
            'validation_img.required' => '请上传营业执照照片'
        ];

    }
}
