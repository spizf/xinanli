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
        if($request->username){
            $data['list']=DB::table('experts')->where('name','like','%'.$request->username.'%')->paginate('10');
            $data['username']=$request->username;
        }else{
            $data['list']=DB::table('experts')->orderBy('id','asc')->paginate('10');
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
        $addr=$request->get('addr');
        foreach($cate as $k=>$v){
            if($v=='0'){
                unset($cate[$k]);
            }
        }
        $data = [
            'name' => $request->get('name'),
            'position' => $request->get('position'),
            'position_level' => $request->get('position_level'),
            'addr' => implode('-',$request->get('addr')),
            'add_time' => date('Y-m-d H:i:s',time()),
            'year' => $request->get('year'),
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
        if(!empty($file))
        {
            $result = \FileClass::uploadFile($file,'sys');
            $result = json_decode($result,true);
            $data = array_add($data,'head_img',$result['data']['url']);
        }
        $status=DB::table('experts')->insert($data);
        if ($status) {
            if($addr) {
                DB::table('district')->whereId($addr[0])->increment('experts_num');
            }
            return redirect('manage/experts')->with(['message' => '操作成功']);
        }
    }
    public function expertsEditHandle(Request $request){
        $cate=$request->get('cate');
        foreach($cate as $k=>$v){
            if($v=='0'){
                unset($cate[$k]);
            }
        }
        $data = [
            'name' => $request->get('name'),
            'position' => $request->get('position'),
            'position_level' => $request->get('position_level'),
            'addr' => implode('-',$request->get('addr')),
            'add_time' => date('Y-m-d H:i:s',time()),
            'year' => $request->get('year'),
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
        $list['list']=DB::table('experts_task')
            ->select('experts.*','task.*','task.status as t_status','experts_task.*','users.name as uname','users.mobile as mobile')
            ->leftJoin('experts','experts_task.experts_id','=','experts.id')
            ->leftJoin('task','experts_task.task_id','=','task.id')
            ->leftJoin('users','task.uid','=','users.id')
            ->where('experts_task.status','!=','0')
            ->orderBy('experts_task.status')
            ->paginate(12);//dd($list);
        return $this->theme->scope('manage.expertsItemList',$list)->render();
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
    public function field(){
        $list['list']=[];
        $data['category_data']=DB::table('field')->where('pid',0)->get();
//        return $this->theme->scope('manage.expertsItemList',$list)->render();
        return $this->theme->scope('manage.fieldList',$data)->render();
    }
    public function ajaxSecond(Request $request)
    {
        $id = intval($request->get('id'));
        if(is_null($id)){
            return response()->json(['errMsg'=>'参数错误！']);
        }
        $province = DB::table('field')->where('pid',$id)->get();
        $data = [
            'province'=>$province,
            'id'=>$id
        ];
        return response()->json($data);
    }
    public function ajaxThird(Request $request)
    {
        $id = intval($request->get('id'));
        if(is_null($id)){
            return response()->json(['errMsg'=>'参数错误！']);
        }
        $area =  DB::table('field')->where('pid',$id)->get();
        return response()->json($area);
    }
    public function fieldDel($id)
    {
        if(is_null($id)){
            return response()->json(['errCode'=>0]);
        }
        $area =  DB::table('field')->where('id',$id)->delete();
        if($area)
            return response()->json(['errCode'=>1,'id'=>$id]);
    }
    public function fieldCreate(Request $request){
        $data = $request->except('_token');


        if(!empty($data['second']) && $data['third']==$data['second']&&empty($data['four']))
        {
            $pid = $data['second'];
        }elseif(!empty($data['third']) && $data['third']!=$data['second']&&empty($data['four']))
        {
            $pid = $data['third'];
        }elseif(!empty($data['four']) && $data['four']!=$data['third'])
        {
            $pid = $data['four'];
        }else{
            $pid=0;
        }

        foreach($data['name'] as $k=>$v)
        {
            $change_ids = explode(' ',$data['change_ids']);
            if(in_array($k,$change_ids)){
                $res=DB::table('field')->whereId($k)->first();
                if($res) {
                    $result = DB::table('field')->where('pid', $pid)->where('id', $k)->update(['name' => $v, 'sort' => $data['sort'][$k]]);
                }else{
                    DB::table('field')->insert(['name' => $v,'pid'=>$pid, 'sort' => $data['sort'][$k]]);
                }
            }
        }
        return redirect()->back()->with(['massage'=>'修改成功！']);
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
}
