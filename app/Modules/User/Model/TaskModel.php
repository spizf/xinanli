<?php

namespace App\Modules\User\Model;

use App\Modules\Employ\Models\EmployUserModel;
use App\Modules\Finance\Model\FinancialModel;
use App\Modules\Manage\Model\MessageTemplateModel;
use App\Modules\Order\Model\OrderModel;
use App\Modules\User\Model\AttachmentModel;
use App\Modules\User\Model\MessageReceiveModel;
use App\Modules\User\Model\UserDetailModel;
use App\Modules\User\Model\UserModel;
use App\Modules\Task\Model\TaskCateModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class TaskModel extends Model
{

    
    protected $table = 'task';
    protected $fillable = [
        'title', 'desc', 'type_id', 'cate_id', 'phone', 'region_limit', 'status', 'bounty', 'bounty_status', 'created_at', 'updated_at',
        'verified_at', 'begin_at', 'end_at', 'delivery_deadline', 'show_cash', 'real_cash', 'deposit_cash', 'province', 'city', 'area',
        'view_count', 'delivery_count', 'uid', 'username', 'worker_num', 'selected_work_at', 'publicity_at', 'checked_at', 'comment_at',
        'top_status', 'task_success_draw_ratio', 'task_fail_draw_ratio', 'engine_status', 'work_status','productNum', 'contacts', 'industry','task_detail','company_name'
    ];
    static public function createTask($data)
    {
        $status = DB::transaction(function () use ($data) {
            $taskTypeAlias = 'zhaobiao';
            if(isset($data['task_id'])){
                $resultData = self::where("id",$data['task_id'])->update([
                    'phone'=>$data['phone'],
                    'cate_id'=>$data['cate_id'],
                    'province'=>$data['province'],
                    'city'=>$data['city'],
                    'area'=>$data['area'],
                    'title'=>$data['title'],
                    'bounty'=>$data['bounty'],
                    'worker_num'=>$data['worker_num'],
                    'type_id'=>$data['type_id'],
                    'begin_at'=>$data['begin_at'],
                    'delivery_deadline'=>$data['delivery_deadline'],
                    'desc'=>$data['desc'],
                    'created_at'=>$data['created_at'],
                    'show_cash'=>$data['show_cash'],
                    'status'=>$data['status'],
                    'task_success_draw_ratio'=>$data['task_success_draw_ratio'],
                    'task_fail_draw_ratio'=>$data['task_fail_draw_ratio'],
                    //add by xl �������������������ϵ��
                    'productNum'=>$data['productNum'],
                    'contacts'=>$data['contacts'],
                    'industry'=>$data['industry']
                ]);
                $result['id']=$data['task_id'];
            }else{
                $result = self::create($data);
            }
            if (!empty($data['file_id'])) {

                $file_able_ids = AttachmentModel::fileAble($data['file_id']);
                $file_able_ids = array_flatten($file_able_ids);
                if(isset($data['task_id'])){
                    TaskAttachmentModel::where('task_id',$data['task_id'])->delete();
                }
                foreach ($file_able_ids as $v) {
                    $attachment_data = [
                        'task_id' => $result['id'],
                        'attachment_id' => $v,
                        'created_at' => date('Y-m-d H:i:s', time()),
                    ];

                    TaskAttachmentModel::create($attachment_data);
                }

                $attachmentModel = new AttachmentModel();
                $attachmentModel->statusChange($file_able_ids);
            }

            if (!empty($data['product'])) {
                if(isset($data['task_id'])){
                    TaskServiceModel::where('task_id',$data['task_id'])->delete();
                }
                foreach ($data['product'] as $k => $v) {
                    if($taskTypeAlias == 'xuanshang'){
                        $server = ServiceModel::where('id', $v)->first();
                        if ($server['identify'] == 'ZHIDING') {
                            self::where('id', $result['id'])->increment('top_status',1);
                        }
                        if ($server['identify'] == 'JIAJI') {
                            self::where('id', $result['id'])->increment('top_status',1);
                        }
                        if ($server['identify'] == 'SOUSUOYINGQINGPINGBI') {
                            self::where('id', $result['id'])->update(['engine_status' => 1]);
                        }
                        if ($server['identify'] == 'GAOJIANPINGBI') {
                            self::where('id', $result['id'])->update(['work_status' => 1]);
                        }
                    }

                    $service_data = [
                        'task_id' => $result['id'],
                        'service_id' => $v,
                        'created_at' => date('Y-m-d H:i:s', time()),
                    ];

                    TaskServiceModel::create($service_data);

                }
            }

            switch($taskTypeAlias){
                case 'xuanshang':
                    break;
                case 'zhaobiao':

                    UserDetailModel::where('uid', $data['uid'])->increment('publish_task_num', 1);
                    break;
            }
            return $result;
        });
        return $status;
    }



}