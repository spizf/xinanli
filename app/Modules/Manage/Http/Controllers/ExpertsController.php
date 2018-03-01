<?php
namespace App\Modules\Manage\Http\Controllers;

use App\Http\Controllers\ManageController;
use App\Http\Requests;
use App\Modules\Manage\Model\ManagerModel;
use App\Modules\Manage\Model\MenuPermissionModel;
use App\Modules\Manage\Model\ModuleTypeModel;
use App\Modules\Manage\Model\Permission;
use App\Modules\Manage\Model\PermissionRoleModel;
use App\Modules\Manage\Model\Role;
use App\Modules\Manage\Model\RoleUserModel;
use App\Modules\User\Model\DistrictModel;
use App\Modules\User\Model\UserModel;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExpertsController extends ManageController
{
	
    public function __construct()
    {
        parent::__construct();
        $this->initTheme('manage');
        $this->theme->setTitle('仲裁专家管理');
        $this->theme->set('manageType', 'User');
    }

    
    public function showExperts(Request $request)
    {
        if(session('expertsAddr')) {
            $data1=session('expertsAddr');
            $where = '';
            if ($data1['addr1'] !== "-1") {
                if ($data1['addr2'] !== '-1') {
                    if ($data1['addr3'] !== '-1') {
                        $where .= $data1['addr1'].'-'.$data1['addr2'].'-' . $data1['addr3'];
                    } else {
                        $where .= $data1['addr1'] . '-' . $data1['addr2'] . '%';
                    }
                } else {
                    $where .= $data1['addr1'] . '-%';
                }
            }
        }
        if($request->username){
            $data['list']=DB::table('experts')->where('name','like','%'.$request->username.'%');
            if(isset($where)&&$where)
                $data['list']= $data['list']->where('addr','like',$where);
            $data['list']= $data['list']->paginate('10');
            $data['username']=$request->username;
        }else{
            $data['list']=DB::table('experts');
            if(isset($where)&&$where)
                $data['list']= $data['list']->where('addr','like',$where);
            $data['list']=$data['list']->orderBy('id','asc')->paginate('10');
        }
        foreach($data['list'] as $k=>$v){
            foreach($v as $kk=>$vv){
                if($kk=='addr'){
                    $addr=explode('-',$vv);
                    $addr_db=DB::table('district')->whereIn('id',$addr)->get();
                    $arr=[];
                    foreach($addr_db as $val){
                        $arr[]=$val->name;
                    }
                    $data['list'][$k]->$kk = implode('-', $arr);
                }
            }
        }
        $data['district']=DB::table('district')->where('upid',0)->get();
        $this->theme->setTitle('专家列表');
 		return $this->theme->scope('manage.expertsList', $data)->render();
    }
    public function expertsAdd(Request $request)
    {
        $data['addr']=DB::table('district')->where('upid',0)->get();
        $data['cate']=DB::table('cate as a')
            ->select('a.*')
            ->join('cate as b','a.pid','=','b.id')
            ->where('a.pid','!=',0)
            ->get();
        $data['position']=DB::table('position')->get();
        $data['position_level']=DB::table('position_level')->get();
        $actArr=[];
        if(is_array($data['cate'])) {
            foreach ($data['cate'] as $v) {
                $actArr[$v->id] = $v->name;
            }
        }
        $data['act_json']=$actArr;
        $field = \DB::table('field')->where('pid',0)->get();
        $data['field'] = $field;
        return $this->theme->scope('manage.expertsAdd',$data)->render();
    }
    public function ajaxGetAddr(Request $request)
    {
        $id=$request->get('id');
        $data['addr']=DB::table('district')->where('upid',$id)->get();
        echo \GuzzleHttp\json_encode($data['addr']);
    }

    public function expertsAddHandle(Request $request){
        $cate=$request->get('cate');
        $industry=$request->get('industry');
        $son_industry=$request->get('son_industry');
        $addr=$request->get('addr');
        $str='';
        foreach ($cate as $k=>$v){
            if ($v==0){
                unset($cate[$k]);
                unset($industry[$k]);
                unset($son_industry[$k]);
            }
        }
        //筛选仲裁专家用
        foreach($cate as $k=>$v){
            if($k){
                $str.=','.$v.'-'.$industry[$k].'-'.$son_industry[$k];
            }else{
                $str.=$v.'-'.$industry[$k].'-'.$son_industry[$k];
            }
        }
        $data = [
            'name' => $request->get('name'),
            'tell' => $request->get('tell'),
            'position' => $request->get('position'),
            'position_level' => $request->get('position_level'),
            'addr' => implode('-',$request->get('addr')),
            'add_time' => date('Y-m-d H:i:s',time()),
            'year' => $request->get('year'),
            'cates' => $str,
            'cate' => implode('-',$cate),
            'industry' => implode('-',$industry),
            'son_industry' => implode('-',$son_industry),
            'level' => $request->get('level'),
            'recommend' => $request->get('recommend'),
            'satisfaction' => $request->get('satisfaction'),
            'is_show_jigou' => $request->get('is_show_jigou'),
            'ask_num' => $request->get('ask_num'),
            'service_num' => $request->get('service_num'),
            'do_time' => $request->get('do_time'),
            'un_school' => $request->get('un_school'),
            'un_time' => date('Y-m-d H:i:s',strtotime($request->get('un_time'))),
            'un_learn' => $request->get('un_learn'),
            'un_certificate' => $request->get('un_certificate'),
            'un_cer_time' => date('Y-m-d H:i:s',strtotime($request->get('un_cer_time'))),
            'detail' => $request->get('detail')
        ];
        //注册专家用户
        $salt = \CommonClass::random(4);
        $validationCode = \CommonClass::random(6);
        $date = date('Y-m-d H:i:s');
        $now = time();
        $userArr = array(
            'name' => $request->get('name'),
//            'mobile' => $request->get('tell'),//怕专家手机号已经注册过账号所以没加
            'password' => UserModel::encryptPassword('123456', $salt),
            'alternate_password' => UserModel::encryptPassword('123456', $salt),
            'salt' => $salt,
            'email_status' => 2,
            'status' => 1,
            'last_login_time' => $date,
            'overdue_date' => date('Y-m-d H:i:s', $now + 60*60*3),
            'validation_code' => $validationCode,
            'created_at' => $date,
            'updated_at' => $date,
            'user_type' => 3
        );
        $res = DB::table('users')->insertGetId($userArr);
        $detail = DB::table('user_detail')->insert(['uid'=>$res]);
        //注册专家结束
        $file=$request->file('head_img');
        if(empty($file)){
            return redirect('manage/expertsAdd')->with(['message' => '请上传图片']);
        }
        //限制图片上传的尺寸为200*200
        $size = getimagesize($file);//获取上传图片大小
        $width = $size[0];
        $height = $size[1];
       // $height= ($height/$width)*$width;//等比例高度
        if($width !=200 && $height !=200){
            return redirect('manage/expertsAdd')->with(['message' => '请上传图片大小为200*200']);
        }
        if(!empty($file))
        {
            $result = \FileClass::uploadFile($file,'sys');
            $result = json_decode($result,true);
            $data = array_add($data,'head_img',$result['data']['url']);
        }
        $status=DB::table('experts')->insert($data);
        if ($status && $detail) {
            if($addr) {
                DB::table('district')->whereId($addr[0])->increment('experts_num');
            }
            return redirect('manage/experts')->with(['message' => '添加成功']);
        }
    }
    public function expertsEditHandle(Request $request){
        $cate=$request->get('cate');
        $industry=$request->get('industry');
        $son_industry=$request->get('son_industry');
        $str='';
        foreach ($cate as $k=>$v){
            if ($v==0){
                unset($cate[$k]);
                unset($industry[$k]);
                unset($son_industry[$k]);
            }
        }
        //筛选仲裁专家用
        foreach($cate as $k=>$v){
            if($k){
                $str.=','.$v.'-'.$industry[$k].'-'.$son_industry[$k];
            }else{
                $str.=$v.'-'.$industry[$k].'-'.$son_industry[$k];
            }
        }
        $data = [
            'name' => $request->get('name'),
            'tell' => $request->get('tell'),
            'position' => $request->get('position'),
            'position_level' => $request->get('position_level'),
            'addr' => implode('-',$request->get('addr')),
            'add_time' => date('Y-m-d H:i:s',time()),
            'year' => $request->get('year'),
            'cates' => $str,
            'cate' => implode(',',$cate),
            'level' => $request->get('level'),
            'recommend' => $request->get('recommend'),
            'satisfaction' => $request->get('satisfaction'),
            'is_show_jigou' => $request->get('is_show_jigou'),
            'ask_num' => $request->get('ask_num'),
            'service_num' => $request->get('service_num'),
            'do_time' => $request->get('do_time'),
            'un_school' => $request->get('un_school'),
            'un_time' => date('Y-m-d H:i:s',strtotime($request->get('un_time'))),
            'un_learn' => $request->get('un_learn'),
            'un_certificate' => $request->get('un_certificate'),
            'un_cer_time' => date('Y-m-d H:i:s',strtotime($request->get('un_cer_time'))),
            'detail' => $request->get('detail')
        ];
        $file=$request->file('head_img');
        if(empty($file)){
            return redirect('manage/expertsAdd')->with(['message' => '请上传图片']);
        }
        //限制图片上传的尺寸为200*200
        $size = getimagesize($file);//获取上传图片大小
        $width = $size[0];
        $height = $size[1];
        // $height= ($height/$width)*$width;//等比例高度
        if($width !=200 && $height !=200){
            return redirect('manage/expertsAdd')->with(['message' => '请上传图片大小为200*200']);
        }
        if(!empty($file))
        {
            $result = \FileClass::uploadFile($file,'sys');
            $result = json_decode($result,true);
            $data = array_add($data,'head_img',$result['data']['url']);
        }
        $status=DB::table('experts')->whereId($request->id)->update($data);
        if ($status)
            return redirect('manage/experts')->with(['message' => '操作成功']);
    }
    /*专家信息修改页面*/
    public function expertsEdit($id){
        $data['experts']=DB::table('experts')->where('id',$id)->first();
        $data['addr']=DB::table('district')->where('upid',0)->get();
        $data['cate']=DB::table('cate')->where('pid','!=',0)->get();
        $data['position']=DB::table('position')->get();
        $data['position_level']=DB::table('position_level')->get();
        $data['experts']->addr=explode('-',$data['experts']->addr);
        $data['experts']->cate=explode(',',$data['experts']->cate);
        $data['experts']->un_time=date('d/m/Y',strtotime($data['experts']->un_time));
        $data['experts']->un_cer_time=date('d/m/Y',strtotime($data['experts']->un_cer_time));
        $field = \DB::table('field')->where('pid',0)->get();
        $data['field'] = $field;
        $data['experts']->cates = explode(',',$data['experts']->cates);
        $data['experts']->arr = array();
        foreach ($data['experts']->cates as $val){
            $arr = explode('-',$val);
            $data['experts']->arr = array_merge($data['experts']->arr,$arr);
        }
        $actArr=[];
        if(is_array($data['cate'])) {
            foreach ($data['cate'] as $v) {
                $actArr[$v->id] = $v->name;
            }
        }
        $data['act_json']=$actArr;
        return $this->theme->scope('manage.expertsEdit',$data)->render();
    }
    public function expertsWork($id){
        $data['position']=DB::table('position')->get();
        $data['experts']=DB::table('experts')->whereId($id)->first();
        $data['work']=DB::table('experts_work')->where('eid',$id)->first();
        if(!$data['work']){
            unset($data['work']);
        }else{
            if(strtotime($data['work']->end_time)==0){
                $data['work']->end_time="至今";
            }
        }
        return $this->theme->scope('manage.expertsWorkAdd',$data)->render();
    }
    public function expertsWorkHandle(Request $request){
        $end=$request->get('end_time')=='至今'?0:strtotime($request->get('end_time'));
        $data = [
            'eid' => $request->get('eid'),
            'company' => $request->get('company'),
            'start_time' => date('Y-m-d H:i:s',strtotime($request->get('start_time'))),
            'end_time' => date('Y-m-d H:i:s',$end),
            'position' => $request->get('position'),
            'work' => $request->get('work')
        ];
        $file=$request->file('img');
        if(!empty($file))
        {
            $result = \FileClass::uploadFile($file,'sys');
            $result = json_decode($result,true);
            $data = array_add($data,'img',$result['data']['url']);
        }
        $id=$request->get('id');
        if(isset($id)&&$id>0){
            $data['id']=$request->get('id');
            $res=DB::table('experts_work')->update($data);
        }else{
            $res = DB::table('experts_work')->insert($data);
        }
        if ($res)
            return redirect('manage/experts')->with(['message' => '操作成功']);
    }
    public function arbitration(){
        /*$list['list']=DB::table('experts_task')
            ->select('experts.*','task.*','task.status as t_status','experts_task.*','users.name as uname','users.mobile as mobile')
            ->leftJoin('experts','experts_task.experts_id','=','experts.id')
            ->leftJoin('task','experts_task.task_id','=','task.id')
            ->leftJoin('users','task.uid','=','users.id')
            ->where('experts_task.status','!=','0')
            ->orderBy('experts_task.status')
            ->paginate(12);*///dd($list);
        $list['list'] = DB::table('arbitration_expert')->paginate(12);
        foreach ($list['list'] as $ke => $va){
            $list_arr = explode('-',$va->experts);
            $list['list'][$ke]->ex_ls = $list_arr;
            $list['list'][$ke]->ex_name_zh = DB::table('experts')->select('name','id')->whereIn('id',$va->ex_ls)->where('position_level',1)->get();//组长
            $list['list'][$ke]->ex_name_z = DB::table('experts')->select('name','id')->whereIn('id',$va->ex_ls)->where('position_level',2)->get();//组员
            $list['list'][$ke]->user_task = DB::table('task')->select('users.name','task.title','task.updated_at','task.status')->where('task.id',$va->task_id)->leftJoin('users','task.uid','=','users.id')->first();//发任务人和任务名称
            $list['list'][$ke]->user_work = DB::table('work')->select('users.name')->where('work.task_id',$va->task_id)->where('work.status','1')->leftJoin('users','work.uid','=','users.id')->first();//接任务方
            $list['list'][$ke]->user_zc = DB::table('task_reason')->join('users','task_reason.user_id','=','users.id')->select('users.name','users.mobile','task_reason.reason')->where('task_reason.nums',$va->num)->where('task_reason.task_id',$va->task_id)->first();//仲裁原因，仲裁人,联系方式
        }
        return $this->theme->scope('manage.expertsItemList',$list)->render();
    }
    /*仲裁列表详情*/
    public function arbitrationDetail($id)
    {
        $list['list'] = DB::table('arbitration_expert')->where('id',$id)->first();
            $list_arr = explode('-',$list['list']->experts);
            $list['list']->ex_ls = $list_arr;
            $list['list']->ex_name_zh = DB::table('experts')->whereIn('id',$list['list']->ex_ls)->where('position_level',1)->get();//组长
            $list['list']->ex_name_z = DB::table('experts')->whereIn('id',$list['list']->ex_ls)->where('position_level',2)->get();//组员
            $list['list']->user_zc = DB::table('task_reason')->select('users.name','users.mobile','task_reason.reason')->where('task_reason.nums',$list['list']->num)->leftJoin('users','task_reason.user_id','=','users.id')->where('task_reason.task_id',$list['list']->task_id)->first();//仲裁原因，仲裁人,联系方式
        return $this->theme->scope('manage.arbitrationdetail',$list)->render();
    }
    /*仲裁详情提交*/
    public function arbitrationSubmit(Request $request)
    {
        $result_experts = '';
        if (isset($request->result_experts)){
            $result_experts = implode('-',$request->result_experts);
        }
        $data = [
            'headman' => $request->headman,
            'result_experts' => $result_experts
        ];
        $reason = [
            'reason' => $request->reason
        ];
        DB::table('arbitration_expert')->whereId($request->id)->update($data);
        DB::table('task_reason')->where('task_id',$request->task_id)->where('nums',$request->num)->update($reason);
        return redirect('manage/arbitrationDetail/'.$request->id)->with(['message' => '保存成功']);
    }
    /*仲裁列表删除*/
    public function arbitrationDel($id)
    {
        DB::table('arbitration_expert')->whereId($id)->delete();
        return redirect('manage/arbitration')->with(['message' => '删除成功']);
    }
    public function expertsTaskOver($status,$id){
        //失败
        if($status==0){
            $task_status=10;
        }elseif($status==1){
        //成功
            $task_status=7;
        }else{
            return redirect('manage/arbitration')->with(['message' => '操作失败']);
        }
        if(isset($id)&&isset($task_status)){
            DB::table('experts_task')->whereId($id)->update(['status'=>2]);
            $data=DB::table('experts_task')->whereId($id)->first();
            if($data) {
                $res=DB::table('task')->whereId($data->task_id)->update(['status' => $task_status]);
                if($res){
                    return redirect('manage/arbitration')->with(['message' => '操作成功']);
                }else{
                    return redirect('manage/arbitration')->with(['message' => '操作失败']);
                }
            }else{
                return redirect('manage/arbitration')->with(['message' => '操作失败']);
            }
        }else{
            return redirect('manage/arbitration')->with(['message' => '操作失败']);
        }
    }
    public function expertsDel($id){
        $data=DB::table('experts')->whereId($id)->first();
        if(!$data){
            return redirect('manage/experts')->with(['message' => '专家不存在']);
        }else{
            $res=DB::table('experts')->whereId($id)->delete();
            if($res){
                $del=[];
                foreach($data as $k=>$v){
                    $del[$k]=$v;
                }
                unset($del['id']);
                DB::table('experts_del')->insert($del);
                return redirect('manage/experts')->with(['message' => '操作成功']);
            }else{
                return redirect('manage/experts')->with(['message' => '操作失败']);
            }
        }
    }
    public function showExpertsAddr(Request $request){
        $data=$request->except('_token');
        session(['expertsAddr'=>$data]);
        return 1;
    }
}
