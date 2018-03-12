<?php
namespace App\Modules\Manage\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScopeRequest extends FormRequest
{
	
	public function rules()
	{
		return [
			    'name' => 'required'
		];
	}

	
	public function authorize()
	{
		return true;
	}

	public function messages()
	{
		return [
				'name.required' => '请输入标题'

	    ];
	}
}
