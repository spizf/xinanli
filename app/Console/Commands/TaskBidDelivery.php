<?php

namespace App\Console\Commands;

use App\Modules\Finance\Model\FinancialModel;
use App\Modules\Task\Model\TaskPayTypeModel;
use App\Modules\Task\Model\TaskTypeModel;
use App\Modules\Task\Model\WorkModel;
use App\Modules\User\Model\TaskModel;
use App\Modules\User\Model\UserDetailModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TaskBidDelivery extends Command
{
    
    protected $signature = 'taskBidDelivery';

    
    protected $description = '招标任务交付超时，验收超时';

    
    public function __construct()
    {
        parent::__construct();
    }

    
    public function handle()
    {
        
        $taskTypeId = TaskTypeModel::getTaskTypeIdByAlias('zhaobiao');
        
        $task = TaskModel::where('type_id',$taskTypeId)->where('status',18)->get()->toArray();//报告交付状态任务列表
        
        $filled_tasks = self::filledTasks($task);//超过最大交付时间的还没有交付的任务列表
        //任务交付超时处理
        if(count($filled_tasks)!=0){
            foreach($filled_tasks as $v){
                DB::transaction(function() use($v){
                    //重新定义任务失败的状态为20之前为10
                    TaskModel::where('id',$v['id'])->update(['status'=>20,'end_at'=>date('Y-m-d H:i:s',time())]);
                    
                    
                    $task_fail_percentage = TaskModel::where('id',$v['id'])->first();
                    $task_fail_percentage = $task_fail_percentage['task_fail_draw_ratio'];
                    if($task_fail_percentage!=0){
                        $balance = $v['bounty']*(1-$task_fail_percentage/100);
                    }else{
                        $balance = $v['bounty'];
                    }
                    //add by xl 判断一下是资金托管还是线下付款，资金托管余额返回
                   if($v['bounty_status'] == 1){
                       UserDetailModel::where('uid',$v['uid'])->increment('balance',$balance);
                       $pay_type = 1;
                   }elseif($v['bounty_status'] == 2){//线下付款
                       $pay_type = 5;
                   }

                    
                    $finance_data = [
                        'action'=>7,//7为任务失败退款状态
                        //'pay_type'=>1,
                        'pay_type'=>$pay_type,//1为余额退款方式 5为线下退款
                        'cash'=>$balance,
                        'uid'=>$v['uid'],
                        'created_at'=>date('Y-m-d H:i:s',time()),
                        'updated_at'=>date('Y-m-d H:i:s',time()),
                    ];
                    FinancialModel::create($finance_data);
                });
            }
        }
        //验收付款(现为20天静默期)
        $successed_tasks = self::filledTasks($task,2);//已交付过的
        if(!empty($successed_tasks)){
            
            
            $woker_expired = self::expireTaskWorker($successed_tasks);//已交付的没有超过验收最大时间限制的最终工作者用户ID

            foreach($woker_expired as $k=>$v){
                WorkModel::where('task_id',$k)->whereIn('uid',$v)->update(['status'=>5]);
            }

            
            
            $onwer_expired = self::expireTaskOwner($successed_tasks);//超过验收最大时间限制的稿件ID，且稿件是已交付状态
            $onwer_expired = array_flatten($onwer_expired);//多维数组扁平化为一维
            foreach($onwer_expired as $v){
                $work_data = WorkModel::where('id',$v)->first();

                $data['task_id'] = $work_data['task_id'];
                $data['uid'] = $work_data['uid'];
                $data['work_id'] = $v;
                $data['status'] = 1;

                WorkModel::bidWorkCheck($data);//验收付款
            }
        }


    }
    
    private function expireTaskWorker($data)
    {
        $task_delivery_max_time = \CommonClass::getConfig('bid_delivery_max_time');//招标验收期最大时间限制
        $task_delivery_max_time = $task_delivery_max_time*24*3600;
        $expired_works = [];
        foreach($data as $v)
        {
            if((strtotime($v['checked_at'])+$task_delivery_max_time)>=time())//没有超过验收期
            {
                
                $works = WorkModel::where('task_id',$v['id'])
                    ->where('status',1)
                    ->orWhere('status',0)
                    ->lists('uid')
                    ->toArray();
                
                $works_delivery = WorkModel::where('task_id',$v['id'])
                    ->where('status','>',1)
                    ->where('forbidden',0)->lists('uid')->toArray();
                $works_diff = array_diff($works,$works_delivery);
                $expired_works[$v['id']][] = $works_diff;
            };
        }
        return $expired_works;
    }

    private function expireTaskOwner($data)
    {
        $task_check_time_limit = \CommonClass::getConfig('bid_check_time_limit');//验收期最大时间限制
        $task_check_time_limit = $task_check_time_limit*24*3600;
        $expired_works = [];
        foreach($data as $v)
        {
            
            $works = WorkModel::where('task_id',$v['id'])->where('status',2)->get()->toArray();
            $works_expired = [];
            if(!empty($works)){
                foreach($works as $v) {
                    if((strtotime($v['created_at']) + $task_check_time_limit)<=time()){//超过验收期最大时间限制
                        $works_expired[] = $v['id'];
                    }
                }
            }

            
            $works_delivery = WorkModel::where('task_id',$v['id'])->where('status','>',2)->lists('id')->toArray();
            $works_diff = array_diff($works_expired,$works_delivery);
            if(count($works_diff)>0)
            {
                $expired_works[] = $works_diff;
            }
        }
        return $expired_works;
    }

    
    private function filledTasks($data,$type=1)
    {
        $task_delivery_max_time = \CommonClass::getConfig('bid_delivery_max_time');

        
        $task_delivery_max_time = $task_delivery_max_time*24*3600;
        $filled = [];
        $successed = [];
        foreach($data as $k=>$v)
        {
            
            //$taskPayType = TaskPayTypeModel::where('task_id',$v['id'])
               // ->where('status',1)->first();
            //if(!empty($taskPayType)){//去掉付款方式即付款分阶段
                if((strtotime($v['checked_at'])+$task_delivery_max_time)<=time())
                {
                    
                    $query = WorkModel::where('task_id', $v['id'])->whereIn('status',[2,3,4,5]);
                    $work = $query->count();
                    if ($work == 0) {
                        $filled[] = $v;
                    } else {
                        $successed[] = $v;
                    }
                }
           // }

        }
        if($type==1){
            return $filled;
        }else{
            return $successed;
        }
    }
}
