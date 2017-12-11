<?php
namespace App\Modules\Task\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContractRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'money'  =>'required',
            'task_id'     =>'required',
            'status' =>'required',
            //'file'   =>'required|size:1',

        ];

        return $rules;
    }
    public function messages()
    {
        return [
            'money.required' => '请输入合同金额！',
            'money.numeric'=>'合同金额必须是数值',
            //'file.required'=>'请上传合同附件',
            //'file.size'=>'请上传合同附件',
        ];
    }
}
