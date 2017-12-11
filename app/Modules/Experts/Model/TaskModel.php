<?php

namespace App\Modules\Task\Model;

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
        'top_status', 'task_success_draw_ratio', 'task_fail_draw_ratio', 'engine_status', 'work_status'
    ];
    public function province()
    {
        return $this->hasOne('App\Modules\User\Model\DistrictModel','id','province');
    }
    public function city()
    {
        return $this->hasOne('App\Modules\User\Model\DistrictModel','id','city');
    }
    static public function myTasks($data)
    {
        $query = self::select('task.*', 'tt.name as type_name', 'us.name as nickname', 'ud.avatar', 'tc.name as cate_name', 'province.name as province_name', 'city.name as city_name')
            ->where('task.status', '>', 1)
            ->where('task.status', '<=', 11)->where('task.uid', $data['uid'])->where('bounty_status', 1);
        
        if (isset($data['status']) && $data['status'] != 0) {
            switch ($data['status']) {
                case 1:
                    $status = [3, 4, 6];
                    break;
                case 2:
                    $status = [5];
                    break;
                case 3:
                    $status = [7];
                    break;
                case 4:
                    $status = [8, 9, 10];
                    break;
                case 5:
                    $status = [2, 11];
                    break;
            }
            $query->whereIn('task.status', $status);
        }
        
        if (isset($data['time'])) {
            switch ($data['time']) {
                case 1:
                    $query->whereBetween('task.created_at', [date('Y-m-d H:i:s', strtotime('-1 month')), date('Y-m-d H:i:s', time())]);
                    break;
                case 2:
                    $query->whereBetween('task.created_at', [date('Y-m-d H:i:s', strtotime('-3 month')), date('Y-m-d H:i:s', time())]);
                    break;
                case 3:
                    $query->whereBetween('task.created_at', [date('Y-m-d H:i:s', strtotime('-6 month')), date('Y-m-d H:i:s', time())]);
                    break;
            }

        }

        $data = $query->join('task_type as tt', 'task.type_id', '=', 'tt.id')
            ->leftjoin('district as province', 'province.id', '=', 'task.province')
            ->leftjoin('district as city', 'city.id', '=', 'task.city')
            ->leftjoin('users as us', 'us.id', '=', 'task.uid')
            ->leftjoin('user_detail as ud', 'ud.uid', '=', 'task.uid')
            ->leftjoin('cate as tc', 'tc.id', '=', 'task.cate_id')
            ->orderBy('task.created_at', 'desc')
            ->paginate(5);

        return $data;
    }
    
    public static function findBy($data,$paginate=10)
    {
        $query = self::select('task.*', 'b.name as type_name', 'us.name as user_name')->where('task.status', '>', 2)
            ->where('task.bounty_status', 1)->where('task.status', '<=', 9)->where('begin_at', "<=", date('Y-m-d H:i:s', time()))
            ->orderBy('top_status', 'desc');
        
        if (isset($data['keywords'])) {
            $query = $query->where('task.title', 'like', '%' . e($data['keywords']) . '%');
        }
        
        if (isset($data['category']) && $data['category'] != 0) {
            
            $category_ids = TaskCateModel::findCateIds($data['category']);
            $query->whereIn('cate_id', $category_ids);
        }
        
        if (isset($data['province'])) {
            $query->where('task.province', intval($data['province']));
        }
        if (isset($data['city'])) {
            $query->where('task.city', intval($data['city']));
        }
        if (isset($data['area'])) {
            $query->where('task.area', intval($data['area']));
        }
        
        if (isset($data['status'])) {
            switch ($data['status']) {
                case 1:
                    $status = [4];
                    break;
                case 2:
                    $status = [5];
                    break;
                case 3:
                    $status = [6, 7];
                    break;
                case 4:
                    $status = [8, 9];
                    break;
            }
            $query->whereIn('task.status', $status);
        }
        
        if (isset($data['desc']) && $data['desc'] != 'created_at') {
            $query->orderBy($data['desc'], 'desc');
        } elseif (isset($data['desc']) && $data['desc'] == 'created_at') {
            $query->orderBy('created_at');
        } else {
            $query->orderBy('created_at', 'desc');
        }
        $data = $query->join('task_type as b', 'task.type_id', '=', 'b.id')
            ->leftjoin('users as us', 'us.id', '=', 'task.uid')
            ->paginate($paginate);

        return $data;
    }

    
    static function findByCity($data, $city)
    {
        $query = self::select('task.*', 'b.name as type_name', 'us.name as user_name')->where('task.status', '>', 2)
            ->where('task.bounty_status', 1)->where('task.status', '<=', 9)->where('begin_at', "<=", date('Y-m-d H:i:s', time()))
            ->where('task.region_limit', 1)
            ->orderBy('top_status', 'desc');
        
        if (isset($data['keywords'])) {
            $query = $query->where('task.title', 'like', '%' . e($data['keywords']) . '%');
        }
        
        if (isset($data['category']) && $data['category'] != 0) {
            
            $category_ids = TaskCateModel::findCateIds($data['category']);
            $query->whereIn('cate_id', $category_ids);
        }
        
        if (isset($city)) {
            $query->where(function ($query) use ($city) {
                $query->where('province', $city)->orwhere('city', $city);
            });
        }

        if (isset($data['area'])) {
            $query->where(function ($query) use ($data) {
                $query->where('city', $data['area'])->orwhere('area', $data['area']);
            });
        }
        
        if (isset($data['status'])) {
            switch ($data['status']) {
                case 1:
                    $status = [4];
                    break;
                case 2:
                    $status = [5];
                    break;
                case 3:
                    $status = [6, 7];
                    break;
                case 4:
                    $status = [8, 9];
                    break;
            }
            $query->whereIn('task.status', $status);
        }
        
        if (isset($data['desc']) && $data['desc'] != 'created_at') {
            $query->orderBy($data['desc'], 'desc');
        } elseif (isset($data['desc']) && $data['desc'] == 'created_at') {
            $query->orderBy('created_at');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $data = $query->join('task_type as b', 'task.type_id', '=', 'b.id')
            ->leftjoin('users as us', 'us.id', '=', 'task.uid')
            ->paginate(10);

        return $data;
    }

    
    static public function createTask($data)
    {
        $status = DB::transaction(function () use ($data) {
            $result = self::create($data);
            if (!empty($data['file_id'])) {
                
                $file_able_ids = AttachmentModel::fileAble($data['file_id']);
                $file_able_ids = array_flatten($file_able_ids);

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
                foreach ($data['product'] as $k => $v) {
                    $server = ServiceModel::where('id', $v)->first();
                    if ($server['identify'] == 'ZHIDING') {
                        self::where('id', $result['id'])->update(['top_status' => 1]);
                    }
                    if ($server['identify'] == 'SOUSUOYINGQINGPINGBI') {
                        self::where('id', $result['id'])->update(['engine_status' => 1]);
                    }
                    if ($server['identify'] == 'GAOJIANPINGBI') {
                        self::where('id', $result['id'])->update(['work_status' => 1]);
                    }
                    $service_data = [
                        'task_id' => $result['id'],
                        'service_id' => $v,
                        'created_at' => date('Y-m-d H:i:s', time()),
                    ];
                    TaskServiceModel::create($service_data);
                }
            }
            return $result;
        });
        return $status;
    }


    
    static function findById($id)
    {
        $data = self::select('task.*', 'b.name as cate_name', 'c.name as type_name')
            ->where('task.id', '=', $id)
            ->leftjoin('cate as b', 'task.cate_id', '=', 'b.id')
            ->leftjoin('task_type as c', 'task.type_id', '=', 'c.id')
            ->first();

        return $data;
    }

    
    public function taskMoney($id)
    {
        $bounty = self::select('task.bounty')->where('id', '=', $id)->first();
        $bounty = $bounty['bounty'];
        $service = TaskServiceModel::select('task_service.service_id')
            ->where('task_id', '=', $id)->get()->toArray();
        $service = array_flatten($service);
        $serviceModel = new ServiceModel();
        $service_money = $serviceModel->serviceMoney($service);
        $money = $bounty + $service_money;

        return $money;
    }

    static function employbounty($money, $task_id, $uid, $code, $type = 2)
    {
        $status = DB::transaction(function () use ($money, $task_id, $uid, $code, $type) {
            
            $query = DB::table('user_detail')->where('uid', '=', $uid);
            $query->where(function ($query) {
                $query->where('balance_status', '!=', 1);
            })->decrement('balance', $money);
            
            $data = self::where('id', $task_id)->update(['bounty_status' => 1]);
            
            $financial = [
                'action' => 1,
                'pay_type' => $type,
                'cash' => $money,
                'uid' => $uid,
                'created_at' => date('Y-m-d H:i:s', time())
            ];
            FinancialModel::create($financial);
            
            OrderModel::where('code', $code)->update(['status' => 1]);

            
            self::where('id', '=', $task_id)->update(['status' => 0]);

            
            UserDetailModel::where('uid', $uid)->increment('publish_task_num', 1);
        });

        return is_null($status) ? true : false;
    }

    
    static function bounty($money, $task_id, $uid, $code, $type = 1)
    {
        $status = DB::transaction(function () use ($money, $task_id, $uid, $code, $type) {
            
            $query = DB::table('user_detail')->where('uid', '=', $uid);
            $query->where(function ($query) {
                $query->where('balance_status', '!=', 1);
            })->decrement('balance', $money);
            
            $data = self::where('id', $task_id)->update(['bounty_status' => 1]);
            
            $financial = [
                'action' => 1,
                'pay_type' => $type,
                'cash' => $money,
                'uid' => $uid,
                'created_at' => date('Y-m-d H:i:s', time())
            ];
            FinancialModel::create($financial);
            
            OrderModel::where('code', $code)->update(['status' => 1]);

            
            
            $bounty_limit = \CommonClass::getConfig('task_bounty_limit');
            if ($bounty_limit < $money) {
                self::where('id', '=', $task_id)->update(['status' => 3]);
            } else {
                self::where('id', '=', $task_id)->update(['status' => 2]);
            }
            
            UserDetailModel::where('uid', $uid)->increment('publish_task_num', 1);
        });
        
        if (is_null($status)) {
            
            $task_publish_success = MessageTemplateModel::where('code_name', 'task_publish_success')->where('is_open', 1)->where('is_on_site', 1)->first();
            if ($task_publish_success) {
                $task = self::where('id', $task_id)->first()->toArray();
                $task_status = [
                    'status' => [
                        0 => '暂不发布',
                        1 => '已经发布',
                        2 => '赏金托管',
                        3 => '审核通过',
                        4 => '威客交稿',
                        5 => '雇主选稿',
                        6 => '任务公示',
                        7 => '交付验收',
                        8 => '双方互评'
                    ]
                ];
                $task = \CommonClass::intToString([$task], $task_status);
                $task = $task[0];
                $user = UserModel::where('id', $uid)->first();
                $site_name = \CommonClass::getConfig('site_name');
                $domain = \CommonClass::getDomain();
                
                
                $messageVariableArr = [
                    'username' => $user['name'],
                    'task_number' => $task['id'],
                    'task_title' => $task['title'],
                    'task_status' => $task['status_text'],
                    'website' => $site_name,
                    'href' => $domain . '/task/' . $task['id'],
                    'task_link' => $task['title'],
                    'start_time' => $task['begin_at'],
                    'manuscript_end_time' => $task['delivery_deadline'],
                ];
                $message = MessageTemplateModel::sendMessage('task_publish_success', $messageVariableArr);
                $data = [
                    'message_title' => $task_publish_success['name'],
                    'code' => 'task_publish_success',
                    'message_content' => $message,
                    'js_id' => $user['id'],
                    'message_type' => 2,
                    'receive_time' => date('Y-m-d H:i:s', time()),
                    'status' => 0,
                ];
                MessageReceiveModel::create($data);
            }
        }
        return is_null($status) ? true : false;
    }

    
    static function detail($id)
    {
        $query = self::select('task.*', 'a.name as user_name', 'b.name as type_name', 'c.name as cate_name')
            ->where('task.id', '=', $id);
        
        $query = $query->where(function ($query) {
            $query->where('task.status', '>=', 2);
        });
        $data = $query->join('users as a', 'a.id', '=', 'task.uid')
            ->leftjoin('task_type as b', 'b.id', '=', 'task.type_id')
            ->leftjoin('cate as c', 'c.id', '=', 'task.cate_id')
            ->first();
        return $data;
    }


    
    static function findByCate($cate_id, $id)
    {
        $query = self::where('cate_id', '=', $cate_id);
        $query = $query->where(function ($query) use ($id) {
            $query->where('id', '!=', $id);
        });
        
        $query = $query->where(function ($query) {
            $query->where('status', '>', 2);
        });
        
        $query = $query->where(function ($query) {
            $query->where('delivery_deadline', '>', date('Y-m-d H:i:s', time()));
        });
        $data = $query->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        return $data;
    }

    
    static function isEmployer($task_id, $uid)
    {
        $data = self::where('id', $task_id)->first();
        if ($data['uid'] == $uid)
            return true;
        return false;
    }

    
    static public function distributeBounty($id, $uid)
    {
        
        $bounty = self::where('id', $id)->first();
        $bounty = ($bounty['bounty'] / $bounty['worker_num']) * (1 - sprintf("%.2f", $bounty['task_success_draw_ratio'] / 100));

        $status = DB::transaction(function () use ($bounty, $uid) {
            
            UserDetailModel::where('uid', $uid)->increment('balance', $bounty);
            
            $finance_data = [
                'action' => 2,
                'pay_type' => 1,
                'cash' => $bounty,
                'uid' => $uid,
                'create_at' => date('Y-m-d H:i:s', time())
            ];
            FinancialModel::create($finance_data);
        });

        return is_null($status) ? true : false;
    }


    
    static function employAccept($task, $type)
    {
        $status = DB::transeaction(function () use ($task, $type) {
            
            if ($type == 1) {
                
                TaskModel::where('id', $task['id'])->update(['status' => 3]);
                
                $employee_user = EmployUserModel::where('task_id', $task['id'])->first();
                
                self::distributeBounty($task['id'], $employee_user['uid']);
                $bounty = self::where('id', $task['id'])->first();
                $bounty = ($bounty['bounty'] / $bounty['worker_num']) * (1 - $bounty['task_success_draw_ratio']);
                
                UserDetailModel::where('uid', $employee_user['uid'])->increment('balance', $bounty);
                
                $finance_data = [
                    'action' => 2,
                    'pay_type' => 1,
                    'cash' => $bounty,
                    'uid' => $employee_user['uid'],
                    'create_at' => date('Y-m-d H:i:s', time())
                ];
                FinancialModel::create($finance_data);

            } else if ($type == 2) {

            }
        });
    }

    public function test($data)
    {
        $this->where('status','>',2);
    }
}
