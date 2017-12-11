<?php
namespace App\Modules\Task\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WorkRequest extends FormRequest
{
	public function authorize()
	{
		return true;
	}
	
	public function rules()
	{
		$rules = [
				'desc'=>'required|str_length:5000',
                /*'workexpert' => 'required|string|between:2,5',
                'reviewexpert'=>'required|string|between:2,5',*/

		];

		return $rules;
	}
	public function messages()
	{
		return [
				'desc.required' => '稿件描述不能为空',
				'desc.str_length'=> '字数超过限制',
                /*'workexpert.required' => '请输入作业专家姓名',
                'workexpert.string' => '请输入正确的格式',
                'workexpert.between' => '专家姓名:min - :max 个字符',
                'reviewexpert.required' => '请输入评审专家姓名',
                'reviewexpert.string' => '请输入正确的格式',
                'reviewexpert.between' => '专家姓名:min - :max 个字符',*/
		];
	}
}
