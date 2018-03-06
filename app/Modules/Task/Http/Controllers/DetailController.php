<?php
namespace App\Modules\Task\Http\Controllers;

use App\Http\Controllers\IndexController;
use App\Http\Requests;
use App\Modules\Manage\Model\AgreementModel;
use App\Modules\Manage\Model\MessageTemplateModel;
use App\Modules\Task\Http\Requests\CommentRequest;
use App\Modules\Task\Http\Requests\WorkRequest;
use App\Modules\Task\Model\ArbitrationReportModel;
use App\Modules\Task\Model\TaskAttachmentModel;
use App\Modules\Task\Model\TaskModel;
use App\Modules\Task\Model\TaskPaySectionModel;
use App\Modules\Task\Model\TaskPayTypeModel;
use App\Modules\Task\Model\TaskReasonModel;
use App\Modules\Task\Model\TaskReportModel;
use App\Modules\Task\Model\TaskRightsModel;
use App\Modules\Task\Model\TaskServiceModel;
use App\Modules\Task\Model\TaskTypeModel;
use App\Modules\Task\Model\WorkCommentModel;
use App\Modules\Task\Model\WorkModel;
use App\Modules\User\Model\AttachmentModel;
use App\Modules\User\Model\CommentModel;
use App\Modules\User\Model\DistrictModel;
use App\Modules\User\Model\MessageReceiveModel;
use App\Modules\User\Model\UserDetailModel;
use App\Modules\User\Model\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use App\Modules\Advertisement\Model\AdTargetModel;
use App\Modules\Advertisement\Model\AdModel;
use App\Modules\Advertisement\Model\RePositionModel;
use App\Modules\Advertisement\Model\RecommendModel;
use App\Modules\Manage\Model\ConfigModel;
use Teepluss\Theme\Theme;
use Toplan\TaskBalance\Task;


class DetailController extends IndexController
{
    public function __construct()
    {
        parent::__construct();
        $this->user = Auth::user();
        $this->initTheme('main');
    }
    
	public function index($id,Request $request)
    {
        $this->theme->setTitle('任务详情');

        $data = $request->all();

        $detail = TaskModel::detail($id);
        
        if($detail['engine_status']==1){
            $this->theme->set('engine_status',1);
        }
        
        if(is_null($detail)){
            return redirect()->to('task')->with(['error'=>'您访问的任务未托管！']);
        }
        
        $taskTypeAlias = TaskTypeModel::getTaskTypeAliasById($detail['type_id']);

        
        TaskModel::where('id',$id)->increment('view_count',1);
        //add by xl 增加用户类型 第三方评价机构可以接任务 1为企业2为第三方默认为1
        $usertype = 1;
        $user_type = 3;
        $is_win_bid = 0;
        $is_delivery = 0;
        $is_rights = 0;
        $hasBid = 0;
        $delivery_count = 0;
        $works_rights_count = 0;
        $work_data = [];
        
        if($detail['status']>2 && Auth::check())
        {
            
            if(WorkModel::isWorker($this->user['id'],$detail['id']))
            {
                $user_type = 2;//投标者
                
                $is_win_bid = WorkModel::isWinBid($id,$this->user['id']);
                $is_delivery = WorkModel::where('task_id',$id)->where('status','>',1)->where('uid',$this->user['id'])->first();
                $is_rights = WorkModel::where('task_id',$id)->where('status','=',4)->where('uid',$this->user['id'])->first();

            }
            
            if($detail['uid']==$this->user['id'])
            {
                $user_type = 1;//发布需求者
                $hasBid = WorkModel::where('task_id',$id)->where('status',1)->first();
                if($hasBid){
                    $hasBid = 1;
                }
            }
        }
        $payCaseStatus = 0;
        $paySectionStatus = 0;
        if($taskTypeAlias == 'zhaobiao'){
            $payCase = TaskPayTypeModel::where('task_id',$id)->where('status',1)->first();
            if(!empty($payCase)){
                $payCaseStatus = 1;
            }
            
            $paySection = TaskPaySectionModel::where('task_id',$id)->where('verify_status',0)->where('section_status',1)->first();
            if(!empty($paySection)){
                $paySectionStatus = 1;
            }
        }
        
        $works = WorkModel::findAll($id,$data);
        $works_count = WorkModel::where('task_id',$id)->where('status','<=',1)->where('forbidden',0)->count();
        $works_bid_count = WorkModel::where('task_id',$id)->where('status','=',1)->where('forbidden',0)->count();
        $works_winbid_count = WorkModel::where('task_id',$id)->where('status','=',1)->where('forbidden',0)->count();
        $delivery = [];
        if(Auth::check())
        {
            if($user_type==2)
            {
                if($taskTypeAlias == 'zhaobiao'){
                    $delivery = WorkModel::select('work.*','us.name as nickname','a.avatar','tp.sort','tp.desc as pay_desc')
                        ->where('work.uid',$this->user['id'])
                        ->where('work.task_id',$id)
                        ->where('work.status','>=',2)
                        ->with('childrenAttachment')
                        ->join('user_detail as a','a.uid','=','work.uid')
                        ->leftjoin('users as us','us.id','=','work.uid')
                        ->leftJoin('task_pay_section as tp','tp.work_id','=','work.id')
                        ->paginate(5)->setPageName('delivery_page')->toArray();
                }else{
                    $delivery = WorkModel::select('work.*','us.name as nickname','a.avatar')
                        ->where('work.uid',$this->user['id'])
                        ->where('work.task_id',$id)
                        ->where('work.status','>=',2)
                        ->with('childrenAttachment')
                        ->join('user_detail as a','a.uid','=','work.uid')
                        ->leftjoin('users as us','us.id','=','work.uid')
                        ->paginate(5)->setPageName('delivery_page')->toArray();
                }

                $delivery_count = count($delivery['data']);
            }elseif($user_type==1){
                $delivery = WorkModel::findDelivery($id,$data);
                if($taskTypeAlias == 'zhaobiao'){
                    if(!empty($delivery['data'])){
                        $paySectionWork = TaskPaySectionModel::where('task_id',$id)->where('work_id','!=','')
                            ->select('work_id','sort','desc')->get()->toArray();
                        if(!empty($paySectionWork)){
                            foreach($delivery['data'] as $k => $v){
                                foreach($paySectionWork as $key => $val){
                                    if($v['id'] == $val['work_id']){
                                        $delivery['data'][$k]['sort'] = $val['sort'];
                                        $delivery['data'][$k]['pay_desc'] = $val['desc'];
                                    }
                                }
                            }
                        }
                    }
                }
                $delivery_count = WorkModel::where('task_id',$id)->where('status','>=',2)->count();

            }
        }
        
        $comment = CommentModel::taskComment($id,$data);

        $comment_count = CommentModel::where('task_id',$id)->count();
        
        $good_comment = CommentModel::where('task_id',$id)->where('type',1)->count();
        $middle_comment = CommentModel::where('task_id',$id)->where('type',2)->count();
        $bad_comment = CommentModel::where('task_id',$id)->where('type',3)->count();
        
        $attatchment_ids = TaskAttachmentModel::where('task_id','=',$id)->lists('attachment_id')->toArray();
        $attatchment_ids = array_flatten($attatchment_ids);
        $attatchment = AttachmentModel::whereIn('id',$attatchment_ids)->get();
        //add by xl 获取合同报告
        $contract = array();
        if (DB::table('task_contract')->where('task_id',$id)->get()){
            $contract = DB::table('task_contract')->where('task_id',$id)->first();
            $arraycontract = explode(",",$contract->attachment_id);
            $contract = AttachmentModel::whereIn('id',$arraycontract)->get();
        }
        $alike_task = TaskModel::findByCate($detail['cate_id'],$id);

        
        $works_rights = [];
        if(Auth::check())
        {
            if($user_type==2)
            {
                $works_rights = WorkModel::select('work.*','us.name as nickname','ud.avatar')
                    ->where('work.uid',$this->user['id'])
                    ->where('task_id',$id)->where('work.status',4)
                    ->with('childrenAttachment')
                    ->join('user_detail as ud','ud.uid','=','work.uid')
                    ->leftjoin('users as us','us.id','=','work.uid')
                    ->paginate(5)->setPageName('delivery_page')->toArray();
                $works_rights_count = 1;
            }elseif($user_type==1)
            {
                $works_rights = WorkModel::findRights($id);
                $works_rights_count = WorkModel::where('task_id',$id)->where('status',4)->count();
            }

            if(!empty($works_rights['data'])){
                foreach($works_rights['data'] as $k => $v){
                    $rights = TaskRightsModel::where('task_id',$id)->where('from_uid',$v['uid'])->where('work_id',$v['id'])->first();
                    $works_rights['data'][$k]['rights_desc'] = $rights['desc'];
                }
            }
        }

        $domain = \CommonClass::getDomain();

        
        $ad = AdTargetModel::getAdInfo('TASKINFO_RIGHT');

        
        $reTarget = RePositionModel::where('code','TASKDETAIL_SIDE')->where('is_open','1')->select('id','name')->first();
        if($reTarget->id){
            $recommend = RecommendModel::getRecommendInfo($reTarget->id)->select('*')->get();
            if(count($recommend)){
                foreach($recommend as $k=>$v){
                    $taskInfo = TaskModel::where('id',$v['recommend_id'])->select('bounty','created_at')->first();
                    if($taskInfo){
                        $v['bounty'] = $taskInfo->bounty;
                        $v['create_time'] = $taskInfo->created_at;
                    }
                    else{
                        $v['bounty'] = 0;
                        $v['create_time'] = 0;
                    }
                    $recommend[$k] = $v;
                }
                $hotList = $recommend;
            }
            else{
                $hotList = [];
            }
        }
        
        $agree = AgreementModel::where('code_name','task_delivery')->first();

        //是否仲裁中  task_reason表中nums字段1代表一次仲裁 2代表二次仲裁  task表中zc_status 0代表可以发起一次仲裁 1代表可以发起二次仲裁
        $is_arbitration = TaskReasonModel::where('task_id',$id)->where('nums',$detail['zc_status']+1)->first();
        //判断当前用户是不是发起该任务仲裁的用户
        $is_user = 0;
       if($is_arbitration && ($this->user['id'] == $is_arbitration['user_id'])){
            $is_user = 1;
       }
        $view = [
            'detail'=>$detail,
//           'expertss'=>$expertss,
            'attatchment'=>$attatchment,
            'contract'=>$contract,
            'alike_task'=>$alike_task,
            'user_type'=>$user_type,
            'works'=> $works,
            'file_type'=>'jpg',
            'is_win_bid'=>$is_win_bid,
            'is_delivery'=>$is_delivery,
            'merge'=>$data,
            'delivery'=>$delivery,
            'domain'=>$domain,
            'comment'=>$comment,
            'good_comment'=>$good_comment,
            'middle_comment'=>$middle_comment,
            'bad_comment'=>$bad_comment,
            'works_count'=>$works_count,
            'delivery_count'=>$delivery_count,
            'comment_count'=>$comment_count,
            'works_bid_count'=>$works_bid_count,
            'works_rights'=>$works_rights,
            'works_rights_count'=>$works_rights_count,
            'ad'=>$ad,
            'hotList' => $hotList,
            'targetName' => $reTarget->name,
            'is_rights'=>$is_rights,
            'works_winbid_count'=>$works_winbid_count,
            'agree' => $agree,
            'is_arbitration' => $is_arbitration,
            'is_user' => $is_user,
            'task_type_alias' => $taskTypeAlias,
            'pay_case_status' => $payCaseStatus,
            'pay_section' => $paySectionStatus,
            'has_bid' => $hasBid,
            'usertype' =>$this->user['user_type']
        ];

        //保存仲裁专家
        if ($detail['zc_status']==1 || $detail['zc_status']==2) {
            if (!DB::table('arbitration_expert')->where('task_id', $id)->where('num',$detail['zc_status'])->get()) {
                $str = '';
                //仲裁专家
                $experts = $this->arbitrationExpert($id);
                if ($experts){
                    foreach ($experts as $k => $v) {
                        if ($k) {
                            $str .= '-' . $v->id;
                        } else {
                            $str .= $v->id;
                        }
                    }
                    $arbitration_expert = [
                        'task_id' => $id,
                        'experts' => $str,
                        'num' => $detail['zc_status']
                    ];
                    DB::table('arbitration_expert')->insert($arbitration_expert);
                }
            }
            //获取仲裁专家
            $experts_str = DB::table('arbitration_expert')->where('task_id',$id)->where('num',$detail['zc_status'])->first();
            if ($experts_str){
                $array_experts = explode('-',$experts_str->experts);
                $expertss = $this->getExperts($array_experts);
                $view['expertss'] = $expertss;
            }
            //专家组长
//            if (isset($array_experts)){
//                $group_two = DB::table('experts')
//                    ->whereIn('id',$array_experts)
//                    ->where('position_level',1)
//                    ->select('id','name')
//                    ->get();
//                $view['group_two'] =$group_two;
//            }
            if(!empty($experts_str->headman)){
                $group_two = DB::table('experts')
                    ->where('id',$experts_str->headman)
                    ->select('id','name')
                    ->get();
            }else{
                $group_two = array();
            }

            $view['group_two'] =$group_two;
            /*判断是否筛选过仲裁专家*/
            if ($experts_str->type = 1){
                $view['ex_type'] = 1;
            }else{
                $view['ex_type'] = 0;
            }
        }
        //获取仲裁报告
        if (ArbitrationReportModel::where('task_id',$id)->where('num',$detail['zc_status'])->first()){
            $report = ArbitrationReportModel::where('task_id',$id)->where('num',$detail['zc_status'])->first();
            $arrayExpert = explode("-",$report->attachment);
            $view['zc_report'] = AttachmentModel::whereIn('id',$arrayExpert)->get();
        }

        if($detail['region_limit']==1 && $detail['province'] && $detail['city'] && $detail['area'])
        {
            $province = DistrictModel::whereIn('id',[$detail['province'],$detail['city'],$detail['area']])->get()->toArray();
            $province = \CommonClass::keyBy($province,'id');
            $view['area'] = $province;
        }
        $view['experts']=DB::table('experts_task')
            ->select('experts.*','experts_task.id as etid','position.position')
            ->leftJoin('experts','experts_task.experts_id','=','experts.id')
            ->leftJoin('position','position.id','=','experts.position')
            ->where('task_id',$detail->id)
            ->first();
        if($view['experts']){
            $view['experts']->user=DB::table('users')->where('name',$view['experts']->name)->first();
            $view['experts']->addr=explode('-',$view['experts']->addr);
            foreach($view['experts']->addr as $key=>$item){
                $distirct=DB::table('district')->whereId($item)->first();
                if($distirct) {
                    $view['experts']->addr[$key] = $distirct->name;
                }
            }
            $view['experts']->cate=explode(',',$view['experts']->cate);
            foreach($view['experts']->cate as $key=>$item){
                $distirct=DB::table('cate')->whereId($item)->first();
                if($distirct) {
                    $view['experts']->cate[$key] = $distirct->name;
                }
            }
            $uid=\Auth::id();
            $work=DB::table('work')->where('task_id',$id)->first();
            $wid=isset($work)?$work->uid:0;
            if(isset($uid)&&($uid==$detail->uid||$uid==$wid)){
                $view['experts']->is_user=1;
            }else{
                $view['experts']->is_user=0;
            }
        }
        return $this->theme->scope('task.detail', $view)->render();
    }

    /*return array(expert)*/
    public function getExperts($expert_arr)
    {
        $data['experts'] =  DB::table('experts')->whereIn('experts.id',$expert_arr)->select('experts.*','position.position')
            ->leftJoin('position','experts.position','=','position.id')
            ->get();
        foreach($data['experts'] as $k=>$v){
            foreach($v as $kk=>$vv) {
                $data['experts'][$k]->user=DB::table('users')->where('name',$v->name)->first();
                if($kk=='addr') {
                    $data['experts'][$k]->addr=explode('-',$vv);
                    foreach($data['experts'][$k]->addr as $key=>$item){
                        if($item!==0) {
                            $distirct = DB::table('district')->whereId($item)->first();
                            if($distirct) {
                                $data['experts'][$k]->addr[$key] = $distirct->name;
                            }
                        }
                    }
                }
                if($kk=='cate') {
                    $data['experts'][$k]->cate=explode('-',$vv);
                    foreach($data['experts'][$k]->cate as $key=>$item){
                        if($item!==0) {
                            $distirct = DB::table('cate')->whereId($item)->first();
                            if($distirct) {
                                $data['experts'][$k]->cate[$key] = $distirct->name;
                            }
                        }
                    }
                }
            }
        }
        return $data['experts'];
    }
    
    /*推荐仲裁专家*/
    public function arbitrationExpert($id)
    {
        $evade_one = WorkModel::where('task_id',$id)->where('status','2')->first();
        $evade_two = TaskModel::where('id',$id)->first();
        $pid = DB::table('cate')->where('id',$evade_two['cate_id'])->value('pid');
        $worke = explode('-',$evade_one['workexpert']);
        $reiewe = explode('-',$evade_one['reviewexpert']);
        $name = array_merge($worke,$reiewe);
        //是否为消防或职业病
        if ($pid==167 || $pid==168){
            if ($evade_two['zc_status']==1){
                //一次仲裁
                //先取出两位组长
                $num = $this->expertGroup($evade_two['cate_id'],$evade_two['city'],1,$name,2,1);
                if ($num == 2){
                    //足两位
                    $group_result = $this->expertGroup($evade_two['cate_id'],$evade_two['city'],1,$name,2,2);//组长
                }else{
                    //不足两位
                    $not_enough = 2-$num;//不足的数目
                    if ($not_enough==1){
                        //缺少一位组长
                        $group = $this->expertGroup($evade_two['cate_id'],$evade_two['city'],1,$name,1,3);//市里专家名称
                        $guibi1 = $name;
                        array_push($guibi1,$group);
                        $groups = $this->expertGroup($evade_two['cate_id'],$evade_two['city'],1,$name,1,2);//市里组长
                        $group_one = $this->expertGroup($evade_two['cate_id'],$evade_two['province'],1,$guibi1,1,2);//省里组长（规避市里）
                        $group_result = array_merge($groups,$group_one);//组合组长
                    }elseif ($not_enough==2){
                        $group_result = $this->expertGroup($evade_two['cate_id'],$evade_two['province'],1,$name,2,2);//组长
                    }
                }
                //组员
                $first_group_son_num = $this->expertGroup($evade_two['cate_id'],$evade_two['city'],2,$name,8,1);//市里组员数目
                if ($first_group_son_num==8){
                    //组员足够8位
                    $group_son_result = $this->expertGroup($evade_two['cate_id'],$evade_two['city'],2,$name,8,2);
                    $expert_result = array_merge($group_result,$group_son_result);
                }else{
                    //组员不足8位
                    $not_enough_eight = 8-$first_group_son_num;
                    $group = $this->expertGroup($evade_two['cate_id'],$evade_two['city'],2,$name,8,2);//市里专家名称
                    $guibi2 = $name;
                    for ($i=0;$i<$first_group_son_num;$i++){
                        array_push($guibi2,$group[$i]->name);
                    }
                    $group_son = $this->expertGroup($evade_two['cate_id'],$evade_two['province'],2,$guibi2,$not_enough_eight,2);
                    $group_son_result = array_merge($group,$group_son);
                    $expert_result = array_merge($group_result,$group_son_result);
                }
            }elseif($evade_two['zc_status']==2){
                //规避一次仲裁专家
                $one = DB::table('arbitration_expert')->where('num',1)->where('task_id',$id)->first();
                $list = explode('-',$one->experts);
                $one_list = DB::table('experts')->whereIn('id',$list)->get();
                foreach ($one_list as $k=>$v){
                    array_push($name,$v->name);
                }
                //二次仲裁
                $num = $this->expertGroup($evade_two['cate_id'],$evade_two['city'],1,$name,2,1);//查询市里专家组长数目
                if ($num == 2){
                    //足两位
                    $group_result = $this->expertGroup($evade_two['cate_id'],$evade_two['city'],1,$name,2,2);//组长
                }else{
                    //不足两位
                    $not_enough = 2-$num;//不足的数目
                    if ($not_enough==1){
                        //缺少一位组长
                        $group = $this->expertGroup($evade_two['cate_id'],$evade_two['city'],1,$name,1,3);//市里专家名称
                        $guibi1 = $name;
                        array_push($guibi1,$group);
                        $groups = $this->expertGroup($evade_two['cate_id'],$evade_two['city'],1,$name,1,2);//市里组长
                        $group_one = $this->expertGroup($evade_two['cate_id'],$evade_two['province'],1,$guibi1,1,2);//省里组长（规避市里）
                        $group_result = array_merge($groups,$group_one);//组合组长
                    }elseif ($not_enough==2){
                        $group_result = $this->expertGroup($evade_two['cate_id'],$evade_two['province'],1,$name,2,2);//组长
                    }
                }
                //组员
                $first_group_son_num = $this->expertGroup($evade_two['cate_id'],$evade_two['city'],2,$name,8,1);//市里组员数目
                if ($first_group_son_num==8){
                    //组员足够8位
                    $group_son_result = $this->expertGroup($evade_two['cate_id'],$evade_two['city'],2,$name,8,2);
                    $expert_result = array_merge($group_result,$group_son_result);
                }else{
                    //组员不足8位
                    $not_enough_eight = 8-$first_group_son_num;
                    $group = $this->expertGroup($evade_two['cate_id'],$evade_two['city'],2,$name,8,2);//市里专家名称
                    $guibi2 = $name;
                    for ($i=0;$i<$first_group_son_num;$i++){
                        array_push($guibi2,$group[$i]->name);
                    }
                    $group_son = $this->expertGroup($evade_two['cate_id'],$evade_two['province'],2,$guibi2,$not_enough_eight,2);
                    $group_son_result = array_merge($group,$group_son);
                    $expert_result = array_merge($group_result,$group_son_result);
                }
            }
        }else{
            $chose = $evade_two['cate_id'].'-'.$evade_two['industry'];//行业筛选（暂时不加，留存）
            if ($evade_two['zc_status']==1){
                //第一次仲裁
                $num = $this->expertGroup($evade_two['cate_id'],$evade_two['city'],1,$name,2,1);//查询市里专家组长数目
                if ($num == 2){
                    //足两位
                    $group_result = $this->expertGroup($evade_two['cate_id'],$evade_two['city'],1,$name,2,2);//组长
                }else{
                    //不足两位
                    $not_enough = 2-$num;//不足的数目
                    if ($not_enough==1){
                        //缺少一位组长
                        $group = $this->expertGroup($evade_two['cate_id'],$evade_two['city'],1,$name,1,3);//市里专家名称
                        $guibi1 = $name;
                        array_push($guibi1,$group);
                        $groups = $this->expertGroup($evade_two['cate_id'],$evade_two['city'],1,$name,1,2);//市里组长
                        $group_one = $this->expertGroup($evade_two['cate_id'],$evade_two['province'],1,$guibi1,1,2);//省里组长（规避市里）
                        $group_result = array_merge($groups,$group_one);//组合组长
                    }elseif ($not_enough==2){
                        $group_result = $this->expertGroup($evade_two['cate_id'],$evade_two['province'],1,$name,2,2);//组长
                    }
                }
                //组员
                $first_group_son_num = $this->expertGroup($evade_two['cate_id'],$evade_two['city'],2,$name,8,1);//市里组员数目
                if ($first_group_son_num==8){
                    //组员足够8位
                    $group_son_result = $this->expertGroup($evade_two['cate_id'],$evade_two['city'],2,$name,8,2);
                    $expert_result = array_merge($group_result,$group_son_result);
                }else{
                    //组员不足8位
                    $not_enough_eight = 8-$first_group_son_num;
                    $group = $this->expertGroup($evade_two['cate_id'],$evade_two['city'],2,$name,8,2);//市里专家名称
                    $guibi2 = $name;
                    for ($i=0;$i<$first_group_son_num;$i++){
                        array_push($guibi2,$group[$i]->name);
                    }
                    $group_son = $this->expertGroup($evade_two['cate_id'],$evade_two['province'],2,$guibi2,$not_enough_eight,2);
                    $group_son_result = array_merge($group,$group_son);
                    $expert_result = array_merge($group_result,$group_son_result);
                }
            }elseif ($evade_two['zc_status']==2){
                //规避一次仲裁专家
                $one = DB::table('arbitration_expert')->where('num',1)->where('task_id',$id)->first();
                $list = explode('-',$one->experts);
                $one_list = DB::table('experts')->whereIn('id',$list)->get();
                foreach ($one_list as $k=>$v){
                    array_push($name,$v->name);
                }
                //二次仲裁
                $num = $this->expertGroup($evade_two['cate_id'],$evade_two['city'],1,$name,2,1);//查询市里专家组长数目
                if ($num == 2){
                    //足两位
                    $group_result = $this->expertGroup($evade_two['cate_id'],$evade_two['city'],1,$name,2,2);//组长
                }else{
                    //不足两位
                    $not_enough = 2-$num;//不足的数目
                    if ($not_enough==1){
                        //缺少一位组长
                        $group = $this->expertGroup($evade_two['cate_id'],$evade_two['city'],1,$name,1,3);//市里专家名称
                        $guibi1 = $name;
                        array_push($guibi1,$group);
                        $groups = $this->expertGroup($evade_two['cate_id'],$evade_two['city'],1,$name,1,2);//市里组长
                        $group_one = $this->expertGroup($evade_two['cate_id'],$evade_two['province'],1,$guibi1,1,2);//省里组长（规避市里）
                        $group_result = array_merge($groups,$group_one);//组合组长
                    }elseif ($not_enough==2){
                        $group_result = $this->expertGroup($evade_two['cate_id'],$evade_two['province'],1,$name,2,2);//组长
                    }
                }
                //组员
                $first_group_son_num = $this->expertGroup($evade_two['cate_id'],$evade_two['city'],2,$name,8,1);//市里组员数目
                if ($first_group_son_num==8){
                    //组员足够8位
                    $group_son_result = $this->expertGroup($evade_two['cate_id'],$evade_two['city'],2,$name,8,2);
                    $expert_result = array_merge($group_result,$group_son_result);
                }else{
                    //组员不足8位
                    $not_enough_eight = 8-$first_group_son_num;
                    $group = $this->expertGroup($evade_two['cate_id'],$evade_two['city'],2,$name,8,2);//市里专家名称
                    $guibi2 = $name;
                    for ($i=0;$i<$first_group_son_num;$i++){
                        array_push($guibi2,$group[$i]->name);
                    }
                    $group_son = $this->expertGroup($evade_two['cate_id'],$evade_two['province'],2,$guibi2,$not_enough_eight,2);
                    $group_son_result = array_merge($group,$group_son);
                    $expert_result = array_merge($group_result,$group_son_result);
                }
            }


        }
        return $expert_result;

    }
    //查询专家组长
    /*
     * $cate:专家领域；$area:地域；$level:专家为组长或组员;$name:规避的字段;$take:取出条数;$type:查询方式*/
    public function expertGroup($cate,$area,$level,$name,$take,$type)
    {
        if ($type==1){
            return DB::table('experts')->select('id','name')->where('cates','like','%'.$cate.'%')->where('addr','like','%'.$area.'%')->where('position_level',$level)->whereNotIn('name',$name)->orderBy(DB::raw('RAND()'))->take($take)->count();
        }elseif ($type == 2){
            return DB::table('experts')->select('id','name')->where('cates','like','%'.$cate.'%')->where('addr','like','%'.$area.'%')->where('position_level',$level)->whereNotIn('name',$name)->orderBy(DB::raw('RAND()'))->take($take)->get();
        }elseif ($type == 3){
            return DB::table('experts')->where('cates','like','%'.$cate.'%')->where('addr','like','%'.$area.'%')->where('position_level',$level)->whereNotIn('name',$name)->take($take)->value('name');
        }

    }
    
    /*专家提交仲裁报告*/
    public function submitAccessory(Request $request)
    {
        $data = $request->except('_token');

        $taskModel = new TaskModel();
        $result = $taskModel->reportCreate($data);

        if(!$result) return redirect()->back()->with('error','提交失败！');

        return redirect()->to('task/'.$data['task_id'])->with('error','提交成功！');
    }

    /*筛选专家状态*/
    public function expertFirst(Request $request)
    {
        DB::table('arbitration_expert')
            ->where('task_id', $request->taskid)
            ->update(['type' => 1]);
    }
    
    public function work($id)
    {
        $this->theme->setTitle('竞标接任务');

        
        $agree = AgreementModel::where('code_name','task_draft')->first();

        $task_data = TaskModel::where('id',$id)->first();

        $view =[
            'task'=>$task_data,
            'agree' => $agree
        ];

        return $this->theme->scope('task.work', $view)->render();
    }
    
    public function workCreate(WorkRequest $request)
    {
        $domain = \CommonClass::getDomain();
        $data = $request->except('_token');
        $data['desc'] = \CommonClass::removeXss($data['desc']);
        $data['uid'] = $this->user['id'];
        $data['created_at'] = date('Y-m-d H:i:s',time());


        
        $is_work_able = $this->isWorkAble($data['task_id']);
        
        if(!$is_work_able['able'])
        {
            return redirect()->back()->with('error',$is_work_able['errMsg']);
        }
        
        $workModel = new WorkModel();
        $result = $workModel->workCreate($data);

        if(!$result) return redirect()->back()->with('error','接任务失败！');
        
        
        $task_delivery = MessageTemplateModel::where('code_name','task_delivery')->where('is_open',1)->where('is_on_site',1)->first();
        if($task_delivery)
        {
            $task = TaskModel::where('id',$data['task_id'])->first();
            $user = UserModel::where('id',$task['uid'])->first();

            $site_name = \CommonClass::getConfig('site_name');
            $user_name = Auth::user()['name'];
            
            
            $messageVariableArr = [
                'username'=>$user['name'],
                'name'=>$user_name,
                'href' => $domain.'/task/'.$data['task_id'],
                'task_title'=>$task['title'],
                'website'=>$site_name,
            ];
            $message = MessageTemplateModel::sendMessage('task_delivery',$messageVariableArr);
            $messages = [
                'message_title'=>$task_delivery['name'],
                'code'=>'task_delivery',
                'message_content'=>$message,
                'js_id'=>$user['id'],
                'message_type'=>2,
                'receive_time'=>date('Y-m-d H:i:s',time()),
                'status'=>0,
            ];
            MessageReceiveModel::create($messages);
        }
        return redirect()->to('task/'.$data['task_id']);
    }

    
    public function winBid($work_id,$task_id)
    {
        $data['task_id'] = $task_id;
        $data['work_id'] = $work_id;

        
            
        $task_user = TaskModel::where('id',$task_id)->lists('uid');

        if($task_user[0]!=$this->user['id'])
        {
            return redirect()->back()->with(['error'=>'非法操作,你不是任务的发布者不能选择中标人选！']);
        }
        
        $worker_num = TaskModel::where('id',$task_id)->lists('worker_num');
        
        $win_bid_num = WorkModel::where('task_id',$task_id)->where('status',1)->count();

        
        if($worker_num[0]>$win_bid_num)
        {
            $data['worker_num'] = $worker_num[0];
            $data['win_bid_num'] = $win_bid_num;
            $workModel = new WorkModel();
            $result = $workModel->winBid($data);

            if(!$result) return redirect()->back()->with(['error'=>'操作失败！']);
        }else{
            return redirect()->back()->with(['error'=>'当前中标人数已满！']);
        }

        return redirect()->back()->with(['massage'=>'选标成功！']);
    }

    
    public function delivery($id)
    {
        $this->theme->setTitle('交付稿件');

        $task_data = TaskModel::where('id',$id)->first();

        
        $ad = AdTargetModel::getAdInfo('TASKDELIVERY_RIGHT_BUTTOM');

        
        $agree = AgreementModel::where('code_name','task_delivery')->first();

        
        $reTarget = RePositionModel::where('code','TASKDELIVERY_SIDE')->where('is_open','1')->select('id','name')->first();
        if($reTarget->id){
            $recommend = RecommendModel::getRecommendInfo($reTarget->id)->select('*')->get();
            if(count($recommend)){
                foreach($recommend as $k=>$v){
                    $taskInfo = TaskModel::where('id',$v['recommend_id'])->select('bounty','created_at')->first();
                    if($taskInfo){
                        $v['bounty'] = $taskInfo->bounty;
                        $v['create_time'] = $taskInfo->created_at;
                    }
                    else{
                        $v['bounty'] = 0;
                        $v['create_time'] = 0;
                    }
                    $recommend[$k] = $v;
                }
                $hotList = $recommend;
            }
            else{
                $hotList = [];
            }
        }

        $view =[
            'task'=>$task_data,
            'ad'=>$ad,
            'hotList' => $hotList,
            'targetName' => $reTarget->name,
            'agree' => $agree

        ];

        return $this->theme->scope('task.delivery', $view)->render();
    }

    
    public function deliverCreate(WorkRequest $request)
    {
        $data = $request->except('_token');
        $data['desc'] = \CommonClass::removeXss($data['desc']);
        $data['uid'] = $this->user['id'];
        $data['status'] = 2;
        $data['created_at'] = date('Y-m-d H:i:s',time());
        
        if(empty($data['task_id']) || empty($data['work_id']))
        {
            return redirect()->back()->with(['error'=>'接任务失败']);
        }
        
        if(!WorkModel::isWinBid($data['task_id'],$this->user['id']))
        {
            return redirect()->back()->with('error','您的稿件没有中标不能通过交付！');
        }
        $is_delivery = WorkModel::where('task_id',$data['task_id'])
            ->where('uid',$this->user['id'])
            ->where('status','>=',2)->first();


        
        if($is_delivery)
        {
            return redirect()->back()->with('error','您已经交付过了！');
        }

        $result = WorkModel::delivery($data);

        if(!$result) return redirect()->back()->with('error','交付失败！');
        
        
        $agreement_documents = MessageTemplateModel::where('code_name','agreement_documents')->where('is_open',1)->where('is_on_site',1)->first();
        if($agreement_documents)
        {
            $task = TaskModel::where('id',$data['task_id'])->first();
            $user = UserModel::where('id',$task['uid'])->first();
            $site_name = \CommonClass::getConfig('site_name');
            $user_name = $this->user['name'];
            $domain = \CommonClass::getDomain();
            
            
            $messageVariableArr = [
                'username'=>$user['name'],
                'initiator'=>$user_name,
                'agreement_link'=>$domain.'/task/'.$task['id'],
                'website'=>$site_name,
            ];
            $message = MessageTemplateModel::sendMessage('agreement_documents',$messageVariableArr);
            $messages = [
                'message_title'=>$agreement_documents['name'],
                'code'=>'agreement_documents',
                'message_content'=>$message,
                'js_id'=>$user['id'],
                'message_type'=>2,
                'receive_time'=>date('Y-m-d H:i:s',time()),
                'status'=>0,
            ];
            MessageReceiveModel::create($messages);
        }
        return redirect()->to('task/'.$data['task_id']);
    }

    
    public function workCheck(Request $request)
    {
        $data = $request->except('_token');
        $data['work_status'] = 3;
        $work_data = WorkModel::where('id',$data['work_id'])->first();
        $data['uid'] = $work_data['uid'];
        
        if(!TaskModel::isEmployer($work_data['task_id'],$this->user['id']))
        return redirect()->back()->with(['error'=>'您不是雇主，您的操作有误！']);
        

        if($work_data['status']!=2)
        return redirect()->back()->with(['error'=>'当前稿件不具备验收资格！']);
        
        $worker_num = TaskModel::where('id',$work_data['task_id'])->first();
        $worker_num = $worker_num['worker_num'];
        
        $win_check = WorkModel::where('work.task_id',$work_data['task_id'])->where('status','>',2)->count();

        $data['worker_num'] = $worker_num;
        $data['win_check'] = $win_check;
        $data['task_id'] = $work_data['task_id'];

        $workModel = new WorkModel();
        $result = $workModel->workCheck($data);
        if(!$result) return redirect()->back()->with(['error'=>'验收失败！']);

        if($result) return redirect()->to('task/'.$data['task_id'])->with(['manage'=>'验收成功！']);
    }
    
    public function lostCheck(Request $request)
    {
        $data = $request->except('_token');
        $data['work_status'] = 4;
        
        if(!TaskModel::isEmployer($data['task_id'],$this->user['id']))
            return response()->json(['errCode'=>0,'error'=>'您不是雇主，您的操作有误！']);
        
        $work_data = WorkModel::where('id',$data['work_id'])->first();
        if($work_data['status']!=2)
            return response()->json(['errCode'=>0,'error'=>'当前稿件不具备验收资格！']);

        $workModel = new WorkModel();
        $result = $workModel->lostCheck($data);
        if(!$result) return response()->back()->with('error','验收失败！');
        
        return response()->json(['errCode'=>1,'id'=>$data['work_id']]);
    }
    
    private function isWorkAble($task_id)
    {
        
        $task_data = TaskModel::where('id',$task_id)->first();
        if($task_data['status']!=(3||4) || strtotime($task_data['begin_at'])>time())
        {
            return ['able' => false, 'errMsg' => '当前任务还未开始接任务！'];
        }
        
        if (!isset($this->user['id'])) {
            return ['able' => false, 'errMsg' => '请登录后再操作！'];
        }
        
        if (WorkModel::isWorker($this->user['id'], $task_id)) {
            return ['able' => false, 'errMsg' => '你已经投过稿了'];
        }
        
        if (TaskModel::isEmployer($task_id, $this->user['id']))
        {
            return ['able' => false, 'errMsg'=>'你是任务发布者不能接任务！'];
        }
        return ['able'=>true];
    }

    /*第三方接任务、仲裁，附件上传*/
    public function ajaxWorkAttatchment(Request $request)
    {
        $file = $request->file('file');
        
        $attachment = \FileClass::uploadFile($file,'task');
        $attachment = json_decode($attachment,true);
        
        if($attachment['code']!=200)
        {
            return response()->json(['errCode' => 0, 'errMsg' => $attachment['message']]);
        }
        $attachment_data = array_add($attachment['data'],'status',1);
        $attachment_data['created_at'] = date('Y-m-d H:i:s',time());
        
        $result = AttachmentModel::create($attachment_data);
        $result = json_decode($result,true);

        if(!$result)
        {
            return response()->json(['errCode'=>0,'errMsg'=>'文件上传失败！']);
        }
        
        return response()->json(['id'=>$result['id']]);
    }
    
    public function delAttatchment(Request $request)
    {
        $id = $request->get('id');
        $result = AttachmentModel::where('user_id',$this->user['id'])->where('id',$id)->delete();
        if(!$result)
        {
            return response()->json(['errCode'=>0,'errMsg'=>'删除失败！']);
        }
        return response()->json(['errCode'=>1,'errMsg'=>'删除成功！']);
    }
    /*提交仲裁附件*/
    public function reasonAccessory(Request $request)
    {
        $data = $request->except('_token');

//        if()
//        {
//            return redirect()->back()->with('error','您已提交过附件！');
//        }

        $taskModel = new TaskModel();
        $result = $taskModel->accessoryCreate($data);

        if(!$result) return redirect()->back()->with('error','提交附件失败！');

        return redirect()->to('task/'.$data['task_id'])->with('error','提交附件成功！');
    }
    
    public function download($id)
    {
        $pathToFile = AttachmentModel::where('id',$id)->first();
        $pathToFile = $pathToFile['url'];
        return response()->download($pathToFile);
    }

    
    public function getComment($id)
    {
        $workComment = WorkCommentModel::where('work_id',$id)
            ->with('parentComment')
            ->with('user')
            ->with('users')
            ->get()->toArray();
        
        $domain = \CommonClass::getDomain();
        foreach($workComment as $k=>$v)
        {
            $workComment[$k]['avatar_md5'] = $domain.'/'.$v['user']['avatar'];
            $workComment[$k]['nickname'] = $v['users']['name'];
            if(is_array($v['parent_comment']))
            {
                $workComment[$k]['parent_user'] = $v['parent_comment']['nickname'];
            }
        }
        $data['errCode'] = 1;
        $data['comment'] = $workComment;
        $data['onerror_img'] = \CommonClass::getDomain().'/'.$this->theme->asset()->url('images/defauthead.png');

        return response()->json($data);
    }

    
    public function ajaxComment(CommentRequest $request)
    {
        $data = $request->except('_token');
        $data['comment'] = htmlspecialchars($data['comment']);
        $data['uid'] = $this->user['id'];
        $user = UserDetailModel::where('uid',$this->user['id'])->first();
        $users = UserModel::where('id',$this->user['id'])->first();
        $data['nickname'] = $users['name'];

        $data['created_at'] = date('Y-m-d H:i:s',time());

        
        $result = WorkCommentModel::create($data);

        if(!$result) return response()->json(['errCode'=>0,'errMsg'=>'提交回复失败！']);
        
        $comment_data = WorkCommentModel::where('id',$result['id'])->with('parentComment')->with('user')->with('users')->first()->toArray();
        $domain = \CommonClass::getDomain();
        $comment_data['avatar_md5'] = $domain.'/'.$user['avatar'];

        if(is_array($comment_data['parent_comment']))
        {
            $comment_data['parent_user'] = $comment_data['parent_comment']['nickname'];
        }
        $comment_data['errCode'] = 1;
        $comment_data['onerror_img'] = \CommonClass::getDomain().'/'.$this->theme->asset()->url('images/defauthead.png');

        return response()->json($comment_data);
    }

    
    public function evaluate(Request $request)
    {
        
        $this->theme->setTitle('交易互评');
        $data = $request->all();
        //  add by xl 状态为2报告交付即可评价不需要状态为3验收成功,即看报告是否已交付
        /*$is_checked = WorkModel::where('task_id',$data['id'])
            ->where('uid',$this->user['id'])
            ->where('status',3)->first();*/
        $is_checked = WorkModel::where('task_id',$data['id'])
            ->where('uid',$this->user['id'])
            ->where('status',2)->first();
        
        $task = TaskModel::where('id',$data['id'])->first();

        if(!$is_checked && $task['uid']!=$this->user['id'])
        {
            return redirect()->back()->with('error','你不具备评价资格！');
        }
        
        $alike_task = TaskModel::findByCate($task['cate_id'],$data['id']);
        
        if($is_checked)
        {
            $evaluate_people = UserDetailModel::select('user_detail.*','us.name as nickname')
                ->where('uid',$task['uid'])
                ->join('users as us','user_detail.uid','=','us.id')
                ->first();
            $work = WorkModel::where('id',$data['work_id'])->first();
            $comment_people = UserDetailModel::where('uid',$work['uid'])->first();
            $evaluate_from = 0;
        }else if($task['uid']==$this->user['id'])
        {
            $work = WorkModel::where('id',$data['work_id'])->first();
            $evaluate_people = UserDetailModel::select('user_detail.*','us.name as nickname')
                ->where('uid',$work['uid'])
                ->join('users as us','user_detail.uid','=','us.id')
                ->first();
            $comment_people = UserDetailModel::where('uid',$task['uid'])->first();
            $evaluate_from = 1;
        }
        $domain = \CommonClass::getDomain();

        
        $ad = AdTargetModel::getAdInfo('TASKINFO_RIGHT');

        
        $reTarget = RePositionModel::where('code','TASKDETAIL_SIDE')->where('is_open','1')->select('id','name')->first();
        if($reTarget->id){
            $recommend = RecommendModel::getRecommendInfo($reTarget->id)->select('*')->get();
            if(count($recommend)){
                foreach($recommend as $k=>$v){
                    $taskInfo = TaskModel::where('id',$v['recommend_id'])->select('bounty','created_at')->first();
                    if($taskInfo){
                        $v['bounty'] = $taskInfo->bounty;
                        $v['create_time'] = $taskInfo->created_at;
                    }
                    else{
                        $v['bounty'] = 0;
                        $v['create_time'] = 0;
                    }
                    $recommend[$k] = $v;
                }
                $hotList = $recommend;
            }
            else{
                $hotList = [];
            }
        }
        
        $view = [
            'evaluate_people'=>$evaluate_people,
            'task_id'=>$data['id'],
            'work_id'=>$data['work_id'],
            'domain'=>$domain,
            'comment_people'=>$comment_people,
            'evaluate_from'=>$evaluate_from,
            'alike_task'=>$alike_task,
            'hoteList'=>$hotList,
            'ad'=>$ad,
            'hotList' => $hotList,
            'targetName' => $reTarget->name
        ];

        return $this->theme->scope('task.evaluate', $view)->render();
    }


    
    public function evaluateCreate(Request $request)
    {
        $data = $request->except('token');

        // add by xl 判断报告是否已交付
        /*$is_checked = WorkModel::where('task_id',$data['task_id'])
            ->where('uid',$this->user['id'])
            ->where('status',3)->first();*/
        $is_checked = WorkModel::where('task_id',$data['task_id'])
            ->where('uid',$this->user['id'])
            ->where('status',2)->first();
        
        $task = TaskModel::where('id',$data['task_id'])->first();

        if(!$is_checked && $task['uid']!=$this->user['id']){
            return redirect()->back()->with('error','你不具备评价资格！');
        }
        
        $data['from_uid'] = $this->user['id'];
        $data['comment'] = e($data['comment']);
        $data['created_at'] = date('Y-m-d H:i:s',time());
        
        if($is_checked) {
            $data['to_uid'] = $task['uid'];
            $data['comment_by'] = 0;
        }else if($task['uid']==$this->user['id']) {
            $work = WorkModel::where('id',$data['work_id'])->first();
            $data['to_uid'] = $work['uid'];
            $data['comment_by'] = 1;
        }

        $is_evaluate =  CommentModel::where('from_uid',$this->user['id'])
            ->where('task_id',$data['task_id'])->where('to_uid',$data['to_uid'])
            ->first();

        if($is_evaluate){
            return redirect()->back()->with(['error'=>'你已经评论过了！']);
        }


        $result = CommentModel::commentCreate($data);

        if(!$result) {
            return redirect()->back()->with('error','评论失败！');
        }

        return redirect()->to('task/'.$data['task_id'])->with('massage','评论成功！');
    }

    
    public function ajaxRights(Request $request)
    {
        $data = $request->except('_token');
        $data['desc'] = e($data['desc']);
        $data['status'] = 0;
        $data['created_at'] = date("Y-m-d H:i:s", time());
        $work = WorkModel::where('id',$data['work_id'])->first();
        if($work['status']==4)
        {
            return redirect()->back()->with(['error'=>'当前稿件正在维权']);
        }
        
        $is_checked = WorkModel::where('id',$data['work_id'])
            ->where('status',2)
            ->where('task_id',$data['task_id'])
            ->where('uid',$this->user['id'])
            ->first();
        
        $task = TaskModel::where('id',$data['task_id'])->first();
        
        if(!$is_checked && $task['uid']!=$this->user['id'])
        {
            return redirect()->back()->with(['error'=>'你不具备维权资格！']);
        }
        
        if($is_checked)
        {
            $data['role'] = 0;
            $data['from_uid'] = $this->user['id'];
            $data['to_uid'] = $task['uid'];
        }else if($task['uid']==$this->user['id'])
        {
            $data['role'] = 1;
            $data['from_uid']  = $this->user['id'];

            $data['to_uid'] = $work['uid'];
        }
        $result = TaskRightsModel::rightCreate($data);

        if(!$result)
            return redirect()->back()->with(['error'=>'维权失败！']);
        
        $trading_rights = MessageTemplateModel::where('code_name','trading_rights')->where('is_open',1)->where('is_on_site',1)->first();
        if($trading_rights)
        {

            $task = TaskModel::where('id',$data['task_id'])->first();
            $from_user = UserModel::where('id',$this->user['id'])->first();
            $site_name = \CommonClass::getConfig('site_name');
            
            
            $fromMessageVariableArr = [
                'username'=>$from_user['name'],
                'tasktitle'=>$task['title'],
                'website'=>$site_name,
            ];
            $fromMessage = MessageTemplateModel::sendMessage('trading_rights',$fromMessageVariableArr);
            $messages = [
                'message_title'=>$trading_rights['name'],
                'code'=>'trading_rights',
                'message_content'=>$fromMessage,
                'js_id'=>$from_user['id'],
                'message_type'=>2,
                'receive_time'=>date('Y-m-d H:i:s',time()),
                'status'=>0,
            ];
            MessageReceiveModel::create($messages);
        }
        return redirect()->to('task/'.$data['task_id'])->with(['error'=>'维权成功！']);
    }

    
    public function report(Request $request)
    {
        $domain = \CommonClass::getDomain();
        $data = $request->except('_token');
        $data['desc'] = e($data['desc']);
        $files = $request->file('file');
        
        $is_report = TaskReportModel::where('from_uid',$this->user['id'])
            ->where('task_id',$data['task_id'])
            ->where('work_id',$data['work_id'])
            ->first();

        if($is_report)
        {
            return redirect()->back()->with('error','您已经成功举报过，请等候平台处理!');
        }

        $attachement_ids = [];
        if(!empty($files[0]))
        {
            foreach($files as $v){
                $attachment = \FileClass::uploadFile($v,'task');
                $attachment = json_decode($attachment,true);
                $attachment_data = array_add($attachment['data'],'status',1);
                $attachment_data['created_at'] = date('Y-m-d H:i:s',time());
                
                $result = AttachmentModel::create($attachment_data);
                $attachement_ids[] = $result['id'];
            }
        }
        $work_data = WorkModel::where('id',$data['work_id'])->first();
        
        $data['status'] = 0;
        $data['from_uid'] = $this->user['id'];
        $data['to_uid'] = $work_data['uid'];
        $data['attachment_ids'] = json_encode($attachement_ids);
        $data['created_at'] = date('Y-m-d H:s:i',time());
        $result2 = TaskReportModel::create($data);
        if(!$result2)
        {
            return redirect()->back()->with('error','举报失败，请联系管理员!');
        }
        
        $task_publish_success = MessageTemplateModel::where('code_name','report')->where('is_open',1)->where('is_on_site',1)->first();
        if($task_publish_success)
        {
            $task = TaskModel::where('id',$data['task_id'])->first();
            $site_name = \CommonClass::getConfig('site_name');
            
            $messageVariableArr = [
                'username'=>$this->user['name'],
                'href' => $domain.'/task/'.$data['task_id'],
                'task_title'=>$task['title'],
                'website'=>$site_name,
            ];
            $message = MessageTemplateModel::sendMessage('report ',$messageVariableArr);
            $message = [
                'message_title'=>$task_publish_success['name '],
                'code'=>'report',
                'message_content'=>$message,
                'js_id'=>$this->user['id'],
                'message_type'=>2,
                'receive_time'=>date('Y-m-d H:i:s',time()),
                'status'=>0,
            ];
            MessageReceiveModel::create($message);
        }
        return redirect()->back()->with('message','举报成功!');
    }

    
    public function ajaxPageWorks($id,Request $request)
    {
        $this->initTheme('ajaxpage');
        $data = $request->all();
        $detail = TaskModel::detail($id);

        $taskTypeAlias = TaskTypeModel::getTaskTypeAliasById($detail['type_id']);

        
        $user_type = 3;
        $is_win_bid = 0;
        
        if($detail['status']>2)
        {
            
            if(WorkModel::isWorker($this->user['id'],$detail['id']))
            {
                $user_type = 2;
                
                $is_win_bid = WorkModel::isWinBid($id,$this->user['id']);
            }
            
            if($detail['uid']==$this->user['id'])
            {
                $user_type = 1;
            }
        }

        $works_data = WorkModel::findAll($id,$data);
        $works_count = WorkModel::where('task_id',$id)->where('status','<=',1)->count();
        $works_bid_count = WorkModel::where('task_id',$id)->where('status','=',1)->count();

        $view = [
            'detail'=>$detail,
            'works'=>$works_data,
            'merge'=>$data,
            'works_count'=>$works_count,
            'works_bid_count'=>$works_bid_count,
            'user_type'=>$user_type,
            'is_win_bid'=>$is_win_bid,
            'task_type_alias' => $taskTypeAlias
        ];
        return $this->theme->scope('task.pagework', $view)->render();
    }
    
    public function ajaxPageDelivery($id,Request $request)
    {
        $this->initTheme('ajaxpage');
        $data = $request->all();
        $detail = TaskModel::detail($id);

        
        $user_type = 3;
        $is_win_bid = 0;
        $is_delivery = 0;
        
        if($detail['status']>2)
        {
            
            if(WorkModel::isWorker($this->user['id'],$detail['id']))
            {
                $user_type = 2;
                
                $is_win_bid = WorkModel::isWinBid($id,$this->user['id']);
                $is_delivery = WorkModel::where('task_id',$id)->where('status','>',1)->where('uid',$this->user['id'])->first();
            }
            
            if($detail['uid']==$this->user['id'])
            {
                $user_type = 1;
            }
        }

        $delivery = [];
        if(Auth::check())
        {


            if($user_type==2)
            {
                $delivery = WorkModel::select('work.*','us.name as nickname','a.avatar')
                    ->where('work.uid',$this->user['id'])
                    ->where('work.task_id',$id)
                    ->where('work.status','>=',2)
                    ->with('childrenAttachment')
                    ->join('user_detail as a','a.uid','=','work.uid')
                    ->leftjoin('users as us','us.id','=','work.uid')
                    ->paginate(5)->setPageName('delivery_page')->toArray();
                $delivery_count = 1;
            }elseif($user_type==1){
                $delivery = WorkModel::findDelivery($id,$data);
                $delivery_count = WorkModel::where('task_id',$id)->where('status','>=',2)->count();
            }
        }
        $works_data = WorkModel::findAll($id,$data);

        $domain = \CommonClass::getDomain();
        $view = [
            'detail'=>$detail,
            'delivery'=>$delivery,
            'delivery_count'=>$delivery_count,
            'is_delivery'=>$is_delivery,
            'merge'=>$data,
            'user_type'=>$user_type,
            'is_win_bid'=>$is_win_bid,
            'domain'=>$domain,
            'works'=>$works_data
        ];
        return $this->theme->scope('task.pagedelivery', $view)->render();
    }
    
    public function ajaxPageComment($id,Request $request)
    {
        $this->initTheme('ajaxpage');
        $data = $request->all();
        $detail = TaskModel::detail($id);
        $data['task_user_id'] = $detail['uid'];
        
        $user_type = 3;
        $is_win_bid = 0;
        
        if($detail['status']>2)
        {
            
            if(WorkModel::isWorker($this->user['id'],$detail['id']))
            {
                $user_type = 2;
                
                $is_win_bid = WorkModel::isWinBid($id,$this->user['id']);
            }
            
            if($detail['uid']==$this->user['id'])
            {
                $user_type = 1;
            }
        }
        $delivery = WorkModel::findDelivery($id,$data);
        $works_data = WorkModel::findAll($id,$data);
        
        $comment = CommentModel::taskComment($id,$data);
        $comment_count = CommentModel::where('task_id',$id)->count();
        
        $good_comment = CommentModel::where('task_id',$id)->where('type',1)->count();
        $middle_comment = CommentModel::where('task_id',$id)->where('type',2)->count();
        $bad_comment = CommentModel::where('task_id',$id)->where('type',3)->count();
        $domain = \CommonClass::getDomain();

        $view = [
            'detail'=>$detail,
            'merge'=>$data,
            'user_type'=>$user_type,
            'is_win_bid'=>$is_win_bid,
            'comment'=>$comment,
            'comment_count'=>$comment_count,
            'good_comment'=>$good_comment,
            'middle_comment'=>$middle_comment,
            'bad_comment'=>$bad_comment,
            'delivery'=>$delivery,
            'domain'=>$domain,
            'works'=>$works_data,
            'merge'=>$data
        ];

        return $this->theme->scope('task.pageComment', $view)->render();
    }
    public function rememberTable(Request $request)
    {
        if($request->get('index'))
        {
            setcookie('table_index',$request->get('index'),time()+3600);
        }else{
            setcookie('table_index',1,time()+3600);
        }
    }

    
    public function tenderWork($id)
    {
        $this->theme->setTitle('竞标接任务');

        $uid = Auth::id();
        $task = TaskModel::where('id',$id)->whereIn('status',[3,4,5])->first();
        if(empty($task)){
            return redirect('/task')->with(array('message' => '任务不存在或不能接任务'));
        }
        
        $agree = AgreementModel::where('code_name','task_draft')->first();

        $view = [
            'uid' => $uid,
            'task' => $task,
            'agree' => $agree
        ];


        return $this->theme->scope('task.bid.tenderWork',$view)->render();
    }

    
    public function bidWinBid($work_id,$task_id)
    {
        $data['task_id'] = $task_id;
        $data['work_id'] = $work_id;

        
        
        $task = TaskModel::where('id',$task_id)->first();

        if($task['uid'] != $this->user['id']){
            return redirect()->back()->with(['error'=>'非法操作,你不是任务的发布者不能选择中标人选！']);
        }
        
        $win_bid_num = WorkModel::where('task_id',$task_id)->where('status',1)->count();

        
        if($task['worker_num']>$win_bid_num){
            $data['worker_num'] = $task['worker_num'];
            $data['win_bid_num'] = $win_bid_num;
            $result = WorkModel::bidWinBid($data);

            if(!$result) {
                return redirect()->back()->with(['error'=>'操作失败！']);
            }else{
                /*add by xl 此步骤暂不资金托管*/
                //return redirect('/task/bidBounty/'.$task_id);
                return redirect('/task/'.$task_id);
            }
        }else{
            return redirect()->back()->with(['error'=>'操作失败！']);
        }

    }


    
    public function payType($id)
    {
        $this->theme->setTitle('竞标付款方式');
        $task = TaskModel::find($id);
        $taskPayType = TaskPayTypeModel::where('task_id',$id)->first();
        $paySection = TaskPaySectionModel::where('task_id',$id)->get()->toArray();

        $userType = 3;
        $isWinBid = WorkModel::isWinBid($id,$this->user['id']);
        if($isWinBid){
            $userType = 2;
        }
        if($task['uid']==$this->user['id'])
        {
            $userType = 1;
        }
        $view = [
            'task' => $task,
            'pay_type' => $taskPayType,
            'pay_section' => $paySection,
            'user_type' => $userType
        ];

        return $this->theme->scope('task.bid.payType',$view)->render();
    }

    
    public function ajaxPaySection(Request $request)
    {
        $data = $request->all();
        
        $payType = [
            1 => '100',
            2 => '50:50',
            3 => '50:30:20',
            4 => '自定义'
        ];

        $type = $data['type'];
        $taskId = $data['task_id'];
        $price = TaskModel::find($taskId)->bounty;
        $pay_type_append = isset($data['pay_type_append']) ? $data['pay_type_append'] : array();

        if ($type == 4) {
            $arrPercent = $pay_type_append;
        } else {
            $arrPercent = explode(':', $payType[$type]);
        }

        $html = TaskPaySectionModel::getPaySectionHtml($arrPercent, $price);
        $result = array();
        if ($html) {
            $result['status'] = 'success';
            $result['html'] = $html;
        } else {
            $result['status'] = 'failure';
        }

        return $result;
    }

    
    public function postPayType(Request $request)
    {
        $data = $request->except('_token');

        $task = TaskModel::find($data['task_id']);
        if($task['uid'] != $this->user['id']){
            return redirect()->to('/task/'.$data['task_id'])->with(['message' => '没有权限']);
        }
        $status = TaskPayTypeModel::saveTaskPayType($data);
        if($status){
            return redirect()->to('/task/'.$data['task_id']);
        }else{
            return redirect()->to('/task/'.$data['task_id'])->with(['message' => '操作失败']);
        }
    }

    
    public function checkPayType($taskId,$status)
    {
        $isWinBid = WorkModel::isWinBid($taskId,$this->user['id']);
        if(!$isWinBid){
            return redirect()->to('/task/'.$taskId)->with(['message' => '不是威客,没有权限']);
        }
        $uid = $this->user['id'];
        $status = TaskPayTypeModel::checkTaskPayType($taskId,$status,$uid);
        if($status){
            return redirect()->to('/task/'.$taskId);
        }else{
            return redirect()->to('/task/'.$taskId)->with(['message' => '操作失败']);
        }
    }
    //add by xl 修改任务状态值
    public function changeStatus($taskId,$status){
        $isWinBid = WorkModel::isWinBid($taskId,$this->user['id']);
        if(!$isWinBid){
            return redirect()->to('/task/'.$taskId)->with(['message' => '不是中标者,没有权限']);
        }
       $changes= TaskModel::where('id', $taskId)->update(['status' => $status,'updated_at' => date('Y-m-d H:i:s'),'publicity_at'=>date('Y-m-d H:i:s',time())]);
        if($changes){
            return redirect()->to('/task/'.$taskId);
        }else{
            return redirect()->to('/task/'.$taskId)->with(['message' => '操作失败']);
        }
    }
    //add by xl 第三方不接单的情况下取消他的中标记录并将任务状态改为4
    public function changeWibBid($taskId,$status){
        $isWinBid = WorkModel::isWinBid($taskId,$this->user['id']);
        if(!$isWinBid){
            return redirect()->to('/task/'.$taskId)->with(['message' => '不是中标者,没有权限']);
        }else{
           $work = WorkModel::where(['task_id' => $taskId,'status' => 1,'uid' => $this->user['id']])->update(['status'=>0]);
           if($work){
               return redirect()->to('/changeStatus/'.$taskId.'/'.$status);
           }else{
               return redirect()->to('/task/'.$taskId)->with(['message' => '操作失败']);
           }
        }

    }

    public function payTypeAgain($id)
    {
        $res = TaskPayTypeModel::where('task_id',$id)->delete();
        $result = TaskPaySectionModel::where('task_id',$id)->delete();
        if($res && $result){
            return redirect('/task/payType/'.$id);
        }
    }


    
    /*public function bidDelivery($id)
    {
        $this->theme->setTitle('交付稿件');

        $task = TaskModel::where('id',$id)->first();

        
        $ad = AdTargetModel::getAdInfo('TASKDELIVERY_RIGHT_BUTTOM');

        
        $sort = 1;
        $paySection = TaskPaySectionModel::where('task_id',$id)->orderby('sort','asc')->get()->toArray();
        if(!empty($paySection)){
            foreach($paySection as $k => $v){
                if((!empty($v['work_id']) && $v['verify_status'] == 2) || empty($v['work_id'])){
                    $sort = $v['sort'];
                    break;
                }
            }
        }

        
        $agree = AgreementModel::where('code_name','task_delivery')->first();

        
        $hotList = [];
        $reTarget = RePositionModel::where('code','TASKDELIVERY_SIDE')->where('is_open','1')->select('id','name')->first();
        if($reTarget->id){
            $recommend = RecommendModel::getRecommendInfo($reTarget->id)->select('*')->get();
            if(count($recommend)){
                foreach($recommend as $k=>$v){
                    $taskInfo = TaskModel::where('id',$v['recommend_id'])->select('bounty','created_at')->first();
                    if($taskInfo){
                        $v['bounty'] = $taskInfo->bounty;
                        $v['create_time'] = $taskInfo->created_at;
                    }
                    else{
                        $v['bounty'] = 0;
                        $v['create_time'] = 0;
                    }
                    $recommend[$k] = $v;
                }
                $hotList = $recommend;
            }

        }

        $view =[
            'task' => $task,
            'ad'=>$ad,
            'hotList' => $hotList,
            'targetName' => $reTarget->name,
            'agree' => $agree,
            'sort' => $sort

        ];

        return $this->theme->scope('task.bid.delivery', $view)->render();
    }


    
    public function bidDeliverCreate(WorkRequest $request)
    {
        $data = $request->except('_token');
        $data['desc'] = \CommonClass::removeXss($data['desc']);
        $data['uid'] = $this->user['id'];
        
        if(empty($data['task_id']) || empty($data['sort']))
        {
            return redirect()->back()->with(['error'=>'接任务失败']);
        }
        
        if(!WorkModel::isWinBid($data['task_id'],$this->user['id']))
        {
            return redirect()->back()->with('error','您的稿件没有中标不能通过交付！');
        }

        $result = WorkModel::bidDelivery($data);

        if(!$result) return redirect()->back()->with('error','交付失败！');
        
        
        $agreement_documents = MessageTemplateModel::where('code_name','agreement_documents')->where('is_open',1)->where('is_on_site',1)->first();
        if($agreement_documents)
        {
            $task = TaskModel::where('id',$data['task_id'])->first();
            $user = UserModel::where('id',$task['uid'])->first();
            $site_name = \CommonClass::getConfig('site_name');
            $user_name = $this->user['name'];
            $domain = \CommonClass::getDomain();
            
            
            $messageVariableArr = [
                'username'=>$user['name'],
                'initiator'=>$user_name,
                'agreement_link'=>$domain.'/task/'.$task['id'],
                'website'=>$site_name,
            ];
            $message = MessageTemplateModel::sendMessage('agreement_documents',$messageVariableArr);
            $messages = [
                'message_title'=>$agreement_documents['name'],
                'code'=>'agreement_documents',
                'message_content'=>$message,
                'js_id'=>$user['id'],
                'message_type'=>2,
                'receive_time'=>date('Y-m-d H:i:s',time()),
                'status'=>0,
            ];
            MessageReceiveModel::create($messages);
        }
        return redirect()->to('task/'.$data['task_id']);
    }*/
    // add by xl  重新修订交付改为报告交付
    public function bidDelivery($id)
    {
        $this->theme->setTitle('报告交付');

        $task = TaskModel::where('id',$id)->first();


        $ad = AdTargetModel::getAdInfo('TASKDELIVERY_RIGHT_BUTTOM');

        $agree = AgreementModel::where('code_name','task_delivery')->first();


        $hotList = [];
        $reTarget = RePositionModel::where('code','TASKDELIVERY_SIDE')->where('is_open','1')->select('id','name')->first();
        if($reTarget->id){
            $recommend = RecommendModel::getRecommendInfo($reTarget->id)->select('*')->get();
            if(count($recommend)){
                foreach($recommend as $k=>$v){
                    $taskInfo = TaskModel::where('id',$v['recommend_id'])->select('bounty','created_at')->first();
                    if($taskInfo){
                        $v['bounty'] = $taskInfo->bounty;
                        $v['create_time'] = $taskInfo->created_at;
                    }
                    else{
                        $v['bounty'] = 0;
                        $v['create_time'] = 0;
                    }
                    $recommend[$k] = $v;
                }
                $hotList = $recommend;
            }

        }

        $view =[
            'task' => $task,
            'ad'=>$ad,
            'hotList' => $hotList,
            'targetName' => $reTarget->name,
            'agree' => $agree

        ];

        return $this->theme->scope('task.bid.delivery', $view)->render();
    }



    public function bidDeliverCreate(WorkRequest $request)
    {
        $data = $request->except('_token');
        $data['workexpert'] = implode('-',$data['workexpert']);
        $data['reviewexpert'] = implode('-',$data['reviewexpert']);
       // $data['desc'] = \CommonClass::removeXss($data['desc']);//防sql注入，过滤标签
        $data['desc'] = '';
        $data['workexpert'] = \CommonClass::removeXss($data['workexpert']);//防sql注入，过滤标签
        $data['reviewexpert'] = \CommonClass::removeXss($data['reviewexpert']);//防sql注入，过滤标签
        $data['uid'] = $this->user['id'];
        $data['sort'] = 1;

        if(empty($data['task_id']))
        {
            return redirect()->back()->with(['error'=>'接任务失败']);
        }

        if(!WorkModel::isWinBid($data['task_id'],$this->user['id']))
        {
            return redirect()->back()->with('error','您的稿件没有中标不能通过交付！');
        }
        $result = WorkModel::bidDelivery($data);
        if($result){
            $changes= TaskModel::where('id', $data['task_id'])->update(['status' => 18,'checked_at'=>date('Y-m-d H:i:s',time()),'updated_at' => date('Y-m-d H:i:s'),'publicity_at'=>date('Y-m-d H:i:s',time())]);
            if($changes){
                return redirect()->to('/task/'.$data['task_id']);
            }else{
                return redirect()->to('/task/'.$data['task_id'])->with(['message' => '操作失败']);
            }
        }else{
            return redirect()->back()->with('error','交付失败！');
        }


        $agreement_documents = MessageTemplateModel::where('code_name','agreement_documents')->where('is_open',1)->where('is_on_site',1)->first();
        if($agreement_documents)
        {
            $task = TaskModel::where('id',$data['task_id'])->first();
            $user = UserModel::where('id',$task['uid'])->first();
            $site_name = \CommonClass::getConfig('site_name');
            $user_name = $this->user['name'];
            $domain = \CommonClass::getDomain();


            $messageVariableArr = [
                'username'=>$user['name'],
                'initiator'=>$user_name,
                'agreement_link'=>$domain.'/task/'.$task['id'],
                'website'=>$site_name,
            ];
            $message = MessageTemplateModel::sendMessage('agreement_documents',$messageVariableArr);
            $messages = [
                'message_title'=>$agreement_documents['name'],
                'code'=>'agreement_documents',
                'message_content'=>$message,
                'js_id'=>$user['id'],
                'message_type'=>2,
                'receive_time'=>date('Y-m-d H:i:s',time()),
                'status'=>0,
            ];
            MessageReceiveModel::create($messages);
        }
        return redirect()->to('task/'.$data['task_id']);
    }
    
    public function bidWorkCheck(Request $request)
    {
        $data = $request->except('_token');
        $work_data = WorkModel::where('id',$data['work_id'])->first();
        $data['uid'] = $work_data['uid'];
        
        if(!TaskModel::isEmployer($work_data['task_id'],$this->user['id']))
            return redirect()->back()->with(['error'=>'您不是雇主，您的操作有误！']);
        

        if($work_data['status']!=2){
            return redirect()->back()->with(['error'=>'当前稿件不具备验收资格！']);
        }
        $data['task_id'] = $work_data['task_id'];

        $result = WorkModel::BidWorkCheck($data);
        if(!$result) {
            return redirect()->back()->with(['error'=>'操作失败！']);
        }else{
            return redirect()->to('task/'.$data['task_id'])->with(['manage'=>'操作成功！']);
        }
    }

    
    public function ajaxBidRights(Request $request)
    {
        $data = $request->except('_token');
        $data['desc'] = e($data['desc']);
        $data['status'] = 0;
        $data['created_at'] = date("Y-m-d H:i:s", time());
        $work = WorkModel::where('id',$data['work_id'])->first();
        if($work['status'] == 4){
            return redirect()->back()->with(['error'=>'当前稿件正在维权']);
        }
        
        $is_checked = WorkModel::where('id',$data['work_id'])
            ->whereIn('status',[2,5])
            ->where('task_id',$data['task_id'])
            ->where('uid',$this->user['id'])
            ->first();
        
        $task = TaskModel::where('id',$data['task_id'])->first();
        
        if(!$is_checked && $task['uid']!=$this->user['id']){
            return redirect()->back()->with(['error'=>'你不具备维权资格！']);
        }
        
        if($is_checked){
            $data['role'] = 0;
            $data['from_uid'] = $this->user['id'];
            $data['to_uid'] = $task['uid'];
        }else if($task['uid']==$this->user['id']){
            $data['role'] = 1;
            $data['from_uid']  = $this->user['id'];
            $data['to_uid'] = $work['uid'];
        }
        $result = TaskRightsModel::bidRightCreate($data);

        if(!$result)
            return redirect()->back()->with(['error'=>'维权失败！']);
        
        $trading_rights = MessageTemplateModel::where('code_name','trading_rights')->where('is_open',1)->where('is_on_site',1)->first();
        if($trading_rights)
        {

            $task = TaskModel::where('id',$data['task_id'])->first();
            $from_user = UserModel::where('id',$this->user['id'])->first();
            $site_name = \CommonClass::getConfig('site_name');
            
            
            $fromMessageVariableArr = [
                'username'=>$from_user['name'],
                'tasktitle'=>$task['title'],
                'website'=>$site_name,
            ];
            $fromMessage = MessageTemplateModel::sendMessage('trading_rights',$fromMessageVariableArr);
            $messages = [
                'message_title'=>$trading_rights['name'],
                'code'=>'trading_rights',
                'message_content'=>$fromMessage,
                'js_id'=>$from_user['id'],
                'message_type'=>2,
                'receive_time'=>date('Y-m-d H:i:s',time()),
                'status'=>0,
            ];
            MessageReceiveModel::create($messages);
        }
        return redirect()->to('task/'.$data['task_id'])->with(['error'=>'维权成功！']);
    }

    /*申请仲裁原因提交*/
    public function reasonTask(Request $request)
    {
        $reasons = $request->input('reasons');
        $content = [
            'user_id' => $reasons[2]['value'],
            'employer_id' => $reasons[3]['value'],
            'task_id' => $reasons[1]['value'],
            'reason'  => $reasons[0]['value'],
            'nums'  => $reasons[4]['value']
        ];
        if (TaskReasonModel::create($content)){
            return json_encode(['status'=>1]);
        }else{
            return json_encode(['status'=>0]);
        }

    }
}
