<?php
namespace App\Modules\Manage\Http\Controllers;

use App\Http\Controllers\ManageController;
use App\Modules\Finance\Model\FinancialModel;
use App\Modules\Manage\Model\MessageTemplateModel;
use App\Modules\Task\Model\TaskAttachmentModel;
use App\Modules\Task\Model\TaskExtraModel;
use App\Modules\Task\Model\TaskExtraSeoModel;
use App\Modules\Task\Model\TaskModel;
use App\Modules\Task\Model\TaskTypeModel;
use App\Modules\Task\Model\WorkCommentModel;
use App\Modules\Task\Model\WorkModel;
use App\Modules\User\Model\AttachmentModel;
use App\Modules\User\Model\MessageReceiveModel;
use App\Modules\User\Model\UserDetailModel;
use App\Modules\User\Model\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Theme;

class TaskController extends ManageController
{
    public function __construct()
    {
        parent::__construct();

        $this->initTheme('manage');
        $this->theme->setTitle('任务列表');
        $this->theme->set('manageType', 'task');
    }

    
    public function taskList(Request $request)
    {
        $search = $request->all();
        $by = $request->get('by') ? $request->get('by') : 'id';
        $order = $request->get('order') ? $request->get('order') : 'desc';
        $paginate = $request->get('paginate') ? $request->get('paginate') : 10;

        $taskList = TaskModel::select('task.id', 'us.name', 'task.title', 'task.created_at', 'task.status', 'task.verified_at', 'task.bounty_status');

        if ($request->get('task_title')) {
            $taskList = $taskList->where('task.title', 'like', '%' . $request->get('task_id') . '%');
        }
        if ($request->get('username')) {
            $taskList = $taskList->where('us.name', 'like', '%' . e($request->get('username')) . '%');
        }
        
        if ($request->get('status') && $request->get('status') != 0) {
            switch ($request->get('status')) {
                case 1:
                    $status = [0];
                    break;
                case 2:
                    $status = [1, 2];
                    break;
                case 3:
                    $status = [3, 4, 5, 6, 7, 8];
                    break;
                case 4:
                    $status = [9];
                    break;
                case 5:
                    $status = [10];
                    break;
                case 6:
                    $status = [11];
                    break;
            }
            $taskList = $taskList->whereIn('task.status', $status);
        }
        
        if ($request->get('time_type')) {
            if ($request->get('start')) {
                $start = date('Y-m-d H:i:s', strtotime($request->get('start')));
                $taskList = $taskList->where($request->get('time_type'), '>', $start);
            }
            if ($request->get('end')) {
                $end = date('Y-m-d H:i:s', strtotime($request->get('end')));
                $taskList = $taskList->where($request->get('time_type'), '<', $end);
            }

        }
        $taskList = $taskList->orderBy($by, $order)
            ->leftJoin('users as us', 'us.id', '=', 'task.uid')
            ->paginate($paginate);

        $data = array(
            'task' => $taskList,
        );
        $data['merge'] = $search;

        return $this->theme->scope('manage.tasklist', $data)->render();
    }

    
    public function taskHandle($id, $action)
    {
        $domain = \CommonClass::getDomain();
        if (!$id) {
            return \CommonClass::showMessage('参数错误');
        }
        $id = intval($id);

        switch ($action) {
            
            case 'pass':
                $status = 3;
                break;
            
            case 'deny':
                $status = 10;
                break;
        }
        
        $task = TaskModel::where('id', $id)->first();
        $user = UserModel::where('id', $task['uid'])->first();
        $site_name = \CommonClass::getConfig('site_name');
        if ($status == 3) {
            $result = TaskModel::where('id', $id)->whereIn('status', [1, 2])->update(array('status' => $status,'verified_at'=>date('Y-m-d H:i:s')));
            if (!$result) {
                return redirect()->back()->with(['error' => '操作失败！']);
            }
            $task_audit_failure = MessageTemplateModel::where('code_name', 'audit_success')->where('is_open', 1)->where('is_on_site', 1)->first();
            if ($task_audit_failure) {
                
                $messageVariableArr = [
                    'username' => $user['name'],
                    'website' => $site_name,
                    'task_number' => $task['id'],
                    'task_link' => $domain . '/task/' . $task['id']
                ];
                $message = MessageTemplateModel::sendMessage('audit_success', $messageVariableArr);
                $data = [
                    'message_title' => $task_audit_failure['name'],
                    'code' => 'audit_success',
                    'message_content' => $message,
                    'js_id' => $user['id'],
                    'message_type' => 2,
                    'receive_time' => date('Y-m-d H:i:s', time()),
                    'status' => 0,
                ];
                MessageReceiveModel::create($data);
            }
        } elseif ($status == 10) {
            $result = DB::transaction(function () use ($id, $status, $task) {
                TaskModel::where('id', $id)->whereIn('status', [1, 2])->update(array('status' => $status,'verified_at'=>date('Y-m-d H:i:s')));
                
                if ($task['bounty_status'] == 1) {
                    UserDetailModel::where('uid', $task['uid'])->increment('balance', $task['bounty']);
                    
                    $finance = [
                        'action' => 7,
                        'pay_type' => 1,
                        'cash' => $task['bounty'],
                        'uid' => $task['uid'],
                        'created_at' => date('Y-m-d H:i:d', time()),
                        'updated_at' => date('Y-m-d H:i:d', time())
                    ];
                    FinancialModel::create($finance);
                }
            });
            if (!is_null($result)) {
                return redirect()->back()->with(['error' => '操作失败！']);
            }
            $task_audit_failure = MessageTemplateModel::where('code_name', 'task_audit_failure')->where('is_open', 1)->where('is_on_site', 1)->first();
            if ($task_audit_failure) {
                
                $messageVariableArr = [
                    'username' => $user['name'],
                    'href' => $domain.'/task/'.$task['id'],
                    'task_title' => $site_name,
                    'website' => $site_name,
                ];
                $message = MessageTemplateModel::sendMessage('task_audit_failure', $messageVariableArr);
                $data = [
                    'message_title' => $task_audit_failure['name'],
                    'code' => 'task_audit_failure',
                    'message_content' => $message,
                    'js_id' => $user['id'],
                    'message_type' => 2,
                    'receive_time' => date('Y-m-d H:i:s', time()),
                    'status' => 0,
                ];
                MessageReceiveModel::create($data);
            }

        }
        return redirect()->back()->with(['message' => '操作成功！']);
    }


    
    public function taskMultiHandle(Request $request)
    {
        if (!$request->get('ckb')) {
            return \CommonClass::adminShowMessage('参数错误');
        }
        switch ($request->get('action')) {
            case 'pass':
                $status = 3;
                break;
            case 'deny':
                $status = 10;
                break;
            default:
                $status = 3;
                break;
        }

        $status = TaskModel::whereIn('id', $request->get('ckb'))->where('status', 1)->orWhere('status', 2)->update(array('status' => $status));
        if ($status)
            return back();

    }

    
    public function taskDetail($id)
    {
        $task = TaskModel::where('id', $id)->first();
        if (!$task) {
            return redirect()->back()->with(['error' => '当前任务不存在，无法查看稿件！']);
        }
        $query = TaskModel::select('task.*', 'us.name as nickname', 'ud.avatar', 'ud.qq')->where('task.id', $id);
        $taskDetail = $query->join('user_detail as ud', 'ud.uid', '=', 'task.uid')
            ->leftjoin('users as us', 'us.id', '=', 'task.uid')
            ->first()->toArray();
        if (!$taskDetail) {
            return redirect()->back()->with(['error' => '当前任务已经被删除！']);
        }
        $status = [
            0 => '暂不发布',
            1 => '已经发布',
            2 => '赏金托管',
            3 => '审核通过',
            4 => '威客交稿',
            5 => '雇主选标',
            6 => '任务公示',
            7 => '交付验收',
            8 => '双方互评',
            9 => '任务完成',
            10 => '失败',
            11 => '维权'
        ];
        $taskDetail['status_text'] = $status[$taskDetail['status']];

        
        $taskType = TaskTypeModel::all();
        
        $taskDelivery = WorkModel::where('task_id', $id)->where('status', 3)->count();
        
        $task_attachment = TaskAttachmentModel::select('task_attachment.*', 'at.url')->where('task_id', $id)
            ->leftjoin('attachment as at', 'at.id', '=', 'task_attachment.attachment_id')->get()->toArray();
        
        $task_seo = TaskExtraSeoModel::where('task_id', $id)->first();
        
        $works = WorkModel::select('work.*', 'us.name as nickname', 'ud.avatar')
            ->where('work.status', '<=', 1)
            ->where('work.task_id', $id)
            ->with('childrenAttachment')
            ->leftjoin('user_detail as ud', 'ud.uid', '=', 'work.uid')
            ->leftjoin('users as us', 'us.id', '=', 'work.uid')
            ->get()->toArray();

        
        $task_massages = WorkCommentModel::select('work_comments.*', 'us.name as nickname', 'ud.avatar')
            ->leftjoin('user_detail as ud', 'ud.uid', '=', 'work_comments.uid')
            ->leftjoin('users as us', 'us.id', '=', 'work_comments.uid')
            ->where('work_comments.task_id', $id)->paginate();
        
        $work_delivery = WorkModel::select('work.*', 'us.name as nickname', 'ud.mobile', 'ud.qq', 'ud.avatar')
            ->whereIn('work.status', [2, 3])
            ->where('work.task_id', $id)
            ->with('childrenAttachment')
            ->leftjoin('user_detail as ud', 'ud.uid', '=', 'work.uid')
            ->leftjoin('users as us', 'us.id', '=', 'work.uid')
            ->get()->toArray();

        $domain = \CommonClass::getDomain();

        $data = [
            'task' => $taskDetail,
            'domain' => $domain,
            'taskType' => $taskType,
            'taskDelivery' => $taskDelivery,
            'taskAttachment' => $task_attachment,
            'task_seo' => $task_seo,
            'works' => $works,
            'task_massages' => $task_massages,
            'work_delivery' => $work_delivery
        ];
        return $this->theme->scope('manage.taskdetail', $data)->render();
    }

    
    public function taskDetailUpdate(Request $request)
    {
        $data = $request->except('_token');
        $task_extra = [
            'task_id' => intval($data['task_id']),
            'seo_title' => $data['seo_title'],
            'seo_keyword' => $data['seo_keyword'],
            'seo_content' => $data['seo_content'],
        ];
        $result = TaskExtraSeoModel::firstOrCreate(['task_id' => $data['task_id']])
            ->where('task_id', $data['task_id'])
            ->update($task_extra);
        
        $task = [
            'title' => $data['title'],
            'desc' => $data['desc'],
            'phone' => $data['phone']
        ];
        
        $task_result = TaskModel::where('id', $data['task_id'])->update($task);

        if (!$result || !$task_result) {
            return redirect()->back()->with(['error' => '更新失败！']);
        }

        return redirect()->back()->with(['massage' => '更新成功！']);
    }

    
    public function taskMassageDelete($id)
    {
        $result = WorkCommentModel::destroy($id);

        if (!$result) {
            return redirect()->to('/manage/taskList')->with(['error' => '留言删除失败！']);
        }
        return redirect()->to('/manage/taskList')->with(['massage' => '留言删除成功！']);
    }

    

    public function download($id)
    {
        $pathToFile = AttachmentModel::where('id', $id)->first();
        $pathToFile = $pathToFile['url'];
        return response()->download($pathToFile);
    }
    public function fastTask(Request $request){
        $search = $request->all();
        $taskList = DB::table('fast_task')->select('fast_task.*','manager.realname');

        if ($request->get('task_title')) {
            $taskList = $taskList->where('fast_task.taskName', 'like', '%' . $request->get('task_title') . '%');
        }
        if ($request->get('username')) {
            $taskList = $taskList->where('fast_task.user', 'like', '%' . e($request->get('username')) . '%');
        }
        $taskList=$taskList
            ->leftJoin('manager','fast_task.mid','=','manager.id')
            ->orderBy('fast_task.create_time','desc')
            ->paginate(10);
        $data = array(
            'task' => $taskList,
        );
        $data['merge'] = $search;
        foreach($taskList as $k=>$v){
            $addr=explode('-',$v->addr);
            $addr=DB::table('district')->whereIn('id',$addr)->get();
            $taskList[$k]->addr='';
            foreach($addr as $vb) {
                $taskList[$k]->addr.=$vb->name.'-';
            }
            $taskList[$k]->addr=substr($taskList[$k]->addr,0,strlen($taskList[$k]->addr)-1);
            $industry=explode('-',$v->industry);
            $industry=DB::table('field')->whereIn('id',$industry)->get();
            $taskList[$k]->industry='';
            foreach($industry as $vb) {
                $taskList[$k]->industry.=$vb->name.'-';
            }
            $taskList[$k]->cate=DB::table('cate')->where('id',$taskList[$k]->cate)->first();
            $taskList[$k]->cate=$taskList[$k]->cate->name;
            $taskList[$k]->industry=substr($taskList[$k]->industry,0,strlen($taskList[$k]->industry)-1);
        }
        return $this->theme->scope('manage.fastTasklist', $data)->render();
    }
    public function changeTaskStatus($id,$status){
        if(!$id||!$status){
            return redirect()->to('/manage/fastTask')->with(['massage' => '非法操作！']);
        }else{
            DB::table('fast_task')->whereId($id)->update(['status'=>$status,'mid'=>session('manager')->id]);
        }
    }
}
