<?php
namespace App\Modules\Task\Http\Controllers;

use App\Http\Controllers\IndexController as BasicIndexController;
use App\Http\Requests;
use App\Modules\Manage\Model\AgreementModel;
use App\Modules\Manage\Model\ConfigModel;
use App\Modules\Task\Http\Requests\BountyRequest;
use App\Modules\Task\Http\Requests\TaskRequest;
//add by xl 增加合同表单验证
use App\Modules\Task\Http\Requests\ContractRequest;
use App\Modules\Task\Model\ServiceModel;
use App\Modules\Task\Model\TaskAttachmentModel;
use App\Modules\Task\Model\TaskCateModel;
use App\Modules\Task\Model\TaskModel;
use App\Modules\Task\Model\TaskServiceModel;
use App\Modules\Task\Model\TaskTemplateModel;
use App\Modules\Task\Model\TaskFocusModel;
use App\Modules\Task\Model\TaskTypeModel;
use App\Modules\Task\Model\WorkModel;
use App\Modules\User\Model\AttachmentModel;
use App\Modules\User\Model\BankAuthModel;
use App\Modules\User\Model\DistrictModel;
use App\Modules\User\Model\UserDetailModel;
use App\Modules\User\Model\UserModel;
use App\Modules\Order\Model\OrderModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Theme;
use QrCode;
use App\Modules\Advertisement\Model\AdTargetModel;
use App\Modules\Advertisement\Model\RePositionModel;
use App\Modules\Advertisement\Model\RecommendModel;
use App\Modules\User\Model\CommentModel;
use Cache;
use Omnipay;
use Toplan\TaskBalance\Task;

class IndexController extends BasicIndexController
{
    public function __construct()
    {
        parent::__construct();
        $this->user = Auth::user();
        $this->initTheme('main');
    }

    
    public function tasks(Request $request)
    {
        $this->theme->set('now_menu','/task');
        
        $seoConfig = ConfigModel::getConfigByType('seo');
        if(!empty($seoConfig['seo_task']) && is_array($seoConfig['seo_task'])){
            $this->theme->setTitle($seoConfig['seo_task']['title']);
            $this->theme->set('keywords',$seoConfig['seo_task']['keywords']);
            $this->theme->set('description',$seoConfig['seo_task']['description']);
        }else{
            $this->theme->setTitle('任务大厅');
        }
        
        $data = $request->all();
        /*查询子分类*/
        if (isset($data['category']) && $data['category']!=0) {
            $category = TaskCateModel::findByPid([intval($data['category'])]);
            $pid = $data['category'];
            if (empty($category)) {
                $category_data = TaskCateModel::findById( intval($data['category']));
                $category = TaskCateModel::findByPid([intval($category_data['pid'])]);
                $pid = $category_data['pid'];
            }
        } else {
            
//            $category = TaskCateModel::findByPid([0]);
            $pid = 0;
        }
        /*获取父级分类*/
        $category_one = TaskCateModel::findByPid([0]);

        if (isset($data['province'])) {
            $area_data = DistrictModel::findTree(intval($data['province']));
            $area_pid = $data['province'];
            
            if($this->themeName=='quietgreen') {
                $province = DistrictModel::findTree(0);
                $province_id = $area_pid;
                $city = $area_data;
                $city_id = 0;
                $areas = DistrictModel::findTree($area_data[0]['id']);
                $areas_id = 0;
            }
        } elseif (isset($data['city'])) {
            $area_data = DistrictModel::findTree(intval($data['city']));
            $area_pid = $data['city'];
            
            if($this->themeName=='quietgreen') {
                $province = DistrictModel::findTree(0);
                $city = DistrictModel::findTree($province[0]['id']);
                $city_id = $area_pid;
                $areas = $area_data;
                $areas_id  = 0;
                $province_id = DistrictModel::where('id',$city_id)->first();
                $province_id = $province_id['upid'];
            }
        } elseif (isset($data['area'])) {
            $area = DistrictModel::where('id', '=', intval($data['area']))->first();
            $area_data = DistrictModel::findTree(intval($area['upid']));
            $area_pid = $area['upid'];
            
            if($this->themeName=='quietgreen') {
                $province = DistrictModel::findTree(0);
                $city = DistrictModel::findTree($province[0]['id']);
                $areas = $area_data;
                $areas_id = $data['area'];
                $city_data = DistrictModel::where('id',$area['upid'])->first();
                $city_id = $city_data['id'];
                $province_id = $city_data['upid'];
            }
        } else {
            $area_data = DistrictModel::findTree(0);
            $area_pid = 0;
            
            if($this->themeName=='quietgreen') {
                $province = $area_data;
                $province_id = 0;
                $city = DistrictModel::findTree($area_data[0]['id']);
                $city_id = 0;
                $areas = DistrictModel::findTree($city[0]['id']);
                $areas_id = 0;
            }
        }
        
        $paginate = ($this->themeName == 'black') ? 12 : 10;
        $list = TaskModel::findBy($data,$paginate);

        $lists = $list->toArray();
        if(!empty($lists['data'])){
            foreach($list as $key => $val){
                if((time()-strtotime($val['created_at']))> 0 && (time()-strtotime($val['created_at'])) < 3600){
                    $val['show_publish'] = intval((time()-strtotime($val['created_at']))/60).'分钟前';
                }
                if((time()-strtotime($val['created_at']))> 3600 && (time()-strtotime($val['created_at'])) < 24*3600){
                    $val['show_publish'] = intval((time()-strtotime($val['created_at']))/3600).'小时前';
                }
                if((time()-strtotime($val['created_at']))> 24*3600){
                    $val['show_publish'] = intval((time()-strtotime($val['created_at']))/(24*3600)).'天前';
                }
            }
        }
        $task_ids = array_pluck($lists['data'],['id']);
        $task_service = TaskServiceModel::select('task_service.*','sc.title')->whereIn('task_id',$task_ids)
            ->join('service as sc','sc.id','=','task_service.service_id')
            ->get()->toArray();
        $task_service = \CommonClass::keyByGroup($task_service,'task_id');

        
        $my_focus_task_ids = [];
        if(Auth::check())
        {
            
            $my_focus_task_ids = TaskFocusModel::where('uid',Auth::user()['id'])->lists('task_id');
            $my_focus_task_ids = array_flatten($my_focus_task_ids);
        }

        
        $ad = AdTargetModel::getAdInfo('TASKLIST_BOTTOM');

        
        $rightAd = AdTargetModel::getAdInfo('TASKLIST_RIGHT_TOP');

        
        $hotList = [];
        $reTarget = RePositionModel::where('code','TASKLIST_SIDE')->where('is_open','1')->select('id','name')->first();
        
		$taskType=TaskTypeModel::getTaskTypeAll();
        if($reTarget->id){
            $recommend = RecommendModel::getRecommendInfo($reTarget->id)->select('*')->get();
            if(count($recommend)){
                foreach($recommend as $k=>$v){
                    $comment = CommentModel::where('to_uid',$v['recommend_id'])->count();
                    $goodComment = CommentModel::where('to_uid',$v['recommend_id'])->where('type',1)->count();
                    if($comment){
                        $v['percent'] = $goodComment/$comment;
                    }
                    else{
                        $v['percent'] = 0;
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
            'list_array' => $lists,
            'list'=>$list,
            'merge' => $data,
//            'category' => $category,
            'categoryone' => $category_one,
            'pid' => $pid,
            'area' => $area_data,
            'area_pid' => $area_pid,
            'ad' => $ad,
            'rightAd' => $rightAd,
            'hotList' => $hotList,
            'targetName' => $reTarget->name,
            'my_focus_task_ids' => $my_focus_task_ids,
            'task_service' => $task_service,
			'task_type'=>$taskType
        ];
        /*判断是否查询子分类*/
        if (isset($category)){
            $view['category'] = $category;
        }
        if($this->themeName=='quietgreen')
        {
            $view = array_merge($view,[
                'province'=>$province,
                'city'=>$city,
                'areas'=>$areas,
                'province_id'=>$province_id,
                'city_id'=>$city_id,
                'areas_id'=>$areas_id
            ]);
        }

        
        \CommonClass::taskScheduling();

        return $this->theme->scope('task.tasks', $view)->render();
    }

    
    public function create(Request $request)
    {

        $this->theme->setTitle('发布任务');
        
        $agree = AgreementModel::where('code_name','task_publish')->first();

        
        $hotCate = TaskCateModel::hotCate(6);
        
        $category_all = TaskCateModel::findByPid([0],['id']);
        $category_all = array_flatten($category_all);
        $category_all = TaskCateModel::findByPid($category_all);

        $province = DistrictModel::findTree(0);
        
        $city = DistrictModel::findTree($province[0]['id']);
        
        $area = DistrictModel::findTree($city[0]['id']);
        
        $service = ServiceModel::where('status',1)->where('type',1)->get()->toArray();
        
        $templet_cate = ['设计', '文案', '开发', '装修', '营销', '商务', '生活'];
        $templet = TaskTemplateModel::all();
        
        $taskType = [
            'xuanshang','zhaobiao'
        ];
        $rewardModel = TaskTypeModel::whereIn('alias',$taskType)->get()->toArray();
        
        $phone = \CommonClass::getConfig('phone');
        $qq = \CommonClass::getConfig('qq');
        
        $ad = AdTargetModel::getAdInfo('TASKINFO_RIGHT');
        $field = \DB::table('field')->where('pid',0)->orderby('sort','asc')->get();

        $view = [
            'hotcate' => $hotCate,
            'category_all' => $category_all,
            'province' => $province,
            'area' => $area,
            'city' => $city,
            'service' => $service,
            'templet_cate' => $templet_cate,
            'templet' => $templet,
            'rewardModel'=>$rewardModel,
            'phone'=>$phone,
            'qq'=>$qq,
            'agree' => $agree,
            'ad' => $ad
        ];
        $view['field'] = $field;
        return $this->theme->scope('task.create', $view)->render();
    }

    
    public function createTask(TaskRequest $request)
    {
        $data = $request->except('_token');
        $data['uid'] = $this->user['id'];
        $data['desc'] = \CommonClass::removeXss($data['description']);
        $data['created_at'] = date('Y-m-d H:i:s', time());
        if($data['area']!=0)
        {
            $data['region_limit'] = 1;
        }else{
            $data['region_limit'] = 0;
        }

        //$taskTypeAlias = 'xuanshang';
        $taskTypeAlias = 'zhaobiao';
        if(isset($data['type_id'])){
            $taskType = TaskTypeModel::where('id',$data['type_id'])->first();
            if(!empty($taskType)){
                $taskTypeAlias = $taskType['alias'];
            }
        }
        
        switch($taskTypeAlias){
            case 'xuanshang':
                
                $task_percentage = \CommonClass::getConfig('task_percentage');
                $task_fail_percentage = \CommonClass::getConfig('task_fail_percentage');
                break;
            case 'zhaobiao':
                $task_percentage = \CommonClass::getConfig('bid_percentage');
                $task_fail_percentage = \CommonClass::getConfig('bid_fail_percentage');
                break;
            default:
                $task_percentage = \CommonClass::getConfig('task_percentage');
                $task_fail_percentage = \CommonClass::getConfig('task_fail_percentage');
                break;
        }

        $data['task_success_draw_ratio'] = $task_percentage; 
        $data['task_fail_draw_ratio'] = $task_fail_percentage;

        $data['begin_at'] = preg_replace('/([\x80-\xff]*)/i', '', $data['begin_at'.$taskTypeAlias]);
        $data['delivery_deadline'] = preg_replace('/([\x80-\xff]*)/i', '', $data['delivery_deadline'.$taskTypeAlias]);
        $data['begin_at'] = date('Y-m-d H:i:s', strtotime($data['begin_at']));
        $data['delivery_deadline'] = date('Y-m-d H:i:s', strtotime($data['delivery_deadline']));
        $data['bounty'] = $data['bounty'.$taskTypeAlias];
        $data['show_cash'] = $data['bounty'];
        $data['worker_num'] = $data['worker_num'.$taskTypeAlias];


        
        $controller = '';
        if ($data['slutype'] == 1) {

            switch($taskTypeAlias){
                case 'xuanshang':
                    $data['status'] = 1;
                    $controller = 'bounty';
                    break;
                case 'zhaobiao' :
                    
                    $bid_examine = \CommonClass::getConfig('bid_examine');
                    if($bid_examine == 1){ 
                        $data['status'] = 1;
                    }else{ 
                        $data['status'] = 3;
                    }
                    if(!empty($data['product'])){
                        $controller = 'buyServiceTaskBid';
                    }else{
                        $controller = 'tasksuccess';
                    }
                    break;
                default :
                    $data['status'] = 1;
                    $controller = 'bounty';
                    break;
            }


        } elseif ($data['slutype'] == 2) {
            return redirect()->to('task/preview')->with($data);
        } elseif ($data['slutype'] == 3) {
            $data['status'] = 0;
        }
        //add by xl 存储行业
        $data['industry'] = implode('-',$data['industry']);
        $taskModel = new TaskModel();
		$result = $taskModel->createTask($data);
        if (!$result) {
            return redirect()->back()->with('error', '创建任务失败！');
        }

        if($data['slutype']==3){
            return redirect()->to('user/unreleasedTasks');
        }
        $addr=$data['province'].'-'.$data['city'];
        $cate=$data['cate_id'];
        $expert_1=DB::table('experts')
            ->where('addr','like',$addr.'%')
            ->where('cate','like',$cate.',%')
            ->orWhere('cate','like','%,'.$cate)
            ->orWhere('cate','=',$cate)
            ->orderBy('recommend','desc')
            ->first();
        if($expert_1){
            $expert_data=$expert_1;
        }else{
            $addr=$data['province'];
            $expert_2=DB::table('experts')
                ->where('addr','like',$addr.'%')
                ->where('cate','like',$cate.',%')
                ->orWhere('cate','like','%,'.$cate)
                ->orWhere('cate','=',$cate)
                ->orderBy('recommend','desc')
                ->first();
            if($expert_2){
                $expert_data=$expert_2;
            }else{
                $addr=$data['province'].'-'.$data['city'];
                $expert_3=DB::table('experts')
                    ->where('addr','like',$addr.'%')
                    ->orderBy('recommend','desc')
                    ->first();
                if($expert_3){
                    $expert_data=$expert_3;
                }else{
                    $addr=$data['province'];
                    $expert_4=DB::table('experts')
                        ->where('addr','like',$addr.'%')
                        ->orderBy('recommend','desc')
                        ->first();
                    if($expert_4){
                        $expert_data=$expert_4;
                    }else{
                        $expert_5=DB::table('experts')
                            ->where('cate','like',$cate.',%')
                            ->orWhere('cate','like','%,'.$cate)
                            ->orWhere('cate','=',$cate)
                            ->orderBy('recommend','desc')
                            ->first();
                        if($expert_5) {
                            $expert_data = $expert_5;
                        }else{
                            $expert_6=DB::table('experts')
                                ->orderBy('recommend','desc')
                                ->first();
                            $expert_data = $expert_6;
                        }
                    }
                }
            }
        }
        if(isset($expert_data)&&!empty($expert_data)) {
            $expert_task['experts_id'] = $expert_data->id;
            $expert_task['task_id'] = $result['id'];
            $expert_task['detail'] = '';
            $expert_task['status'] = 0;
            DB::table('experts_task')->insert($expert_task);
            $expert_user=DB::table('users')->where('name',$expert_data->name)->first();
            if($expert_user) {
                //将该专家成为IM好友
                $im[0]['uid'] = $this->user['id'];
                $im[1]['uid'] = $expert_user->id;
                $im[0]['friend_uid'] = $expert_user->id;
                $im[1]['friend_uid'] = $this->user['id'];
                DB::table('im_attention')->insert($im);
            }
        }
        return redirect()->to('task/' . $controller . '/' . $result['id']);
    }
    //add by xl 获取行业列表
    public function getField($id){
        $data['field']=DB::table('field')->where('pid',$id)->get();
        return $data['field'];
    }
    
    public function preview(Request $request)
    {
        $this->theme->setTitle('任务预览');

        $data = $request->session()->all();

        if (empty($data['uid'])) {
            return redirect()->back()->with('error', '数据过期，请重新预览！');
        }

        $user_detail = UserDetailModel::where('uid', $data['uid'])->first();
        $task_cate = TaskCateModel::where('id',$data['cate_id'])->first();
        $attatchment = array();
        if (!empty($data['file_id']) && count($data['file_id']) > 0) {
            
            $file_able_ids = AttachmentModel::fileAble($data['file_id']);
            $file_able_ids = array_flatten($file_able_ids);
            $attatchment = AttachmentModel::whereIn('id', $file_able_ids)->get();
        }
        $phone = \CommonClass::getConfig('phone');
        $qq = \CommonClass::getConfig('qq');
        
        $ad = AdTargetModel::getAdInfo('TASKINFO_RIGHT');
        $taskTypeAlias = TaskTypeModel::getTaskTypeAliasById($data['type_id']);
        $view = [
            'user_detail' => $user_detail,
            'attatchment' => $attatchment,
            'data' => $data,
            'task_cate' => $task_cate,
            'phone'=>$phone,
            'qq'=>$qq,
            'ad' => $ad,
            'task_type_alias' => $taskTypeAlias
        ];
        return $this->theme->scope('task.preview', $view)->render();
    }

    
    public function getTemplate(Request $request)
    {
        $id = $request->get('id');
        if(is_array($id))
            $id = $id[0];
        
        $cate = TaskCateModel::findById($id);
        
        TaskCateModel::where('id',$id)->increment('choose_num',1);
        
        $pid = $cate['pid'];

        $template = TaskTemplateModel::where('cate_id',$pid)->where('status',1)->first();
        if (!$template) {
            return response()->json(['errMsg' => '没有模板']);
        }
        $template['content'] = htmlspecialchars_decode($template['content']);
        return response()->json($template);
    }

    
    public function ajaxTask(TaskRequest $request)
    {
        $data = $request->except('_token');
    }

    
    public function bounty($id)
    {
        $this->theme->setTitle('赏金托管');
        
        $task = TaskModel::findById($id);

        
        if ($task['uid'] != $this->user['id'] || $task['status'] >= 2) {
            return redirect()->back()->with(['error' => '非法操作！']);
        }

        
        $user_money = UserDetailModel::where(['uid' => $this->user['id']])->first();
        $user_money = $user_money['balance'];

        
        $service = TaskServiceModel::select('task_service.service_id')
            ->where('task_id', '=', $id)->get()->toArray();
        $service = array_flatten($service);
        $serviceModel = new ServiceModel();
        $service_money = $serviceModel->serviceMoney($service);

        
        $balance_pay = false;
        if ($user_money > ($task['bounty'] + $service_money)) {
            $balance_pay = true;
        }

        
        $bank = BankAuthModel::where('uid', '=', $id)->where('status', '=', 4)->get();
        
        $payConfig = ConfigModel::getConfigByType('thirdpay');
        $view = [
            'task' => $task,
            'bank' => $bank,
            'service_money' => $service_money,
            'id' => $id,
            'user_money' => $user_money,
            'balance_pay' => $balance_pay,
            'payConfig' => $payConfig
        ];
        return $this->theme->scope('task.bounty', $view)->render();
    }

    
    public function bountyUpdate(BountyRequest $request)
    {
        $data = $request->except('_token');
        $data['id'] = intval($data['id']);
        
        $task = TaskModel::findById($data['id']);

        
        if ($task['uid'] != $this->user['id'] || $task['status'] >= 2) {
            return redirect()->to('/task/' . $task['id'])->with('error', '非法操作！');
        }

        
        $balance = UserDetailModel::where(['uid' => $this->user['id']])->first();
        $balance = (float)$balance['balance'];

        
        $taskModel = new TaskModel();
        $money = $taskModel->taskMoney($data['id']);
        
        $is_ordered = OrderModel::bountyOrder($this->user['id'], $money, $task['id']);

        if (!$is_ordered) return redirect()->back()->with(['error' => '任务托管失败']);

        
        if ($balance >= $money && $data['pay_canel'] == 0)
        {
            
            $password = UserModel::encryptPassword($data['password'], $this->user['salt']);
            if ($password != $this->user['alternate_password']) {
                return redirect()->back()->with(['error' => '您的支付密码不正确']);
            }
            
            $result = TaskModel::bounty($money, $data['id'], $this->user['id'], $is_ordered->code);
            if (!$result) return redirect()->back()->with(['error' => '赏金托管失败！']);
            
            $task = TaskModel::where('id',$data['id'])->first();
            if($task['status']==3){
                $url = 'task/'.$data['id'];
            }elseif($task['status']==2){
                $url = 'task/tasksuccess/'.$data['id'];
            }
            return redirect()->to($url);
        } else if (isset($data['pay_type']) && $data['pay_canel'] == 1) {
            
            if ($data['pay_type'] == 1) {
                $config = ConfigModel::getPayConfig('alipay');
                $objOminipay = Omnipay::gateway('alipay');
                $objOminipay->setPartner($config['partner']);
                $objOminipay->setKey($config['key']);
                $objOminipay->setSellerEmail($config['sellerEmail']);
                $siteUrl = \CommonClass::getConfig('site_url');
                $objOminipay->setReturnUrl($siteUrl . '/order/pay/alipay/return');
                $objOminipay->setNotifyUrl($siteUrl . '/order/pay/alipay/notify');

                $response = Omnipay::purchase([
                    'out_trade_no' => $is_ordered->code, 
                    'subject' => \CommonClass::getConfig('site_name'), 
                    'total_fee' => $money, 
                ])->send();
                $response->redirect();
            } else if ($data['pay_type'] == 2) {
                $config = ConfigModel::getPayConfig('wechatpay');
                $wechat = Omnipay::gateway('wechat');
                $wechat->setAppId($config['appId']);
                $wechat->setMchId($config['mchId']);
                $wechat->setAppKey($config['appKey']);
                $out_trade_no = $is_ordered->code;
                $params = array(
                    'out_trade_no' => $is_ordered->code, 
                    'notify_url' => \CommonClass::getDomain() . '/order/pay/wechat/notify?out_trade_no=' . $out_trade_no . '&task_id=' . $data['id'], 
                    'body' => \CommonClass::getConfig('site_name') . '余额充值', 
                    'total_fee' => $money, 
                    'fee_type' => 'CNY', 
                );
                $response = $wechat->purchase($params)->send();

                $img = QrCode::size('280')->generate($response->getRedirectUrl());

                $view = array(
                    'cash'=>$money,
                    'img' => $img
                );
                return $this->theme->scope('task.wechatpay', $view)->render();
            } else if ($data['pay_type'] == 3) {
                dd('银联支付！');
            }
        } else if (isset($data['account']) && $data['pay_canel'] == 2) {
            dd('银行卡支付！');
        } else
        {
            return redirect()->back()->with(['error' => '请选择一种支付方式']);
        }

    }

    
    public function fileUpload(Request $request)
    {
        $file = $request->file('file');
        
        $attachment = \FileClass::uploadFile($file, 'task');
        $attachment = json_decode($attachment, true);
        
        if($attachment['code']!=200)
        {
            return response()->json(['errCode' => 0, 'errMsg' => $attachment['message']]);
        }
        $attachment_data = array_add($attachment['data'], 'status', 1);
        $attachment_data['created_at'] = date('Y-m-d H:i:s', time());
        
        $result = AttachmentModel::create($attachment_data);
        $result = json_decode($result, true);
        if (!$result) {
            return response()->json(['errCode' => 0, 'errMsg' => '文件上传失败！']);
        }
        
        return response()->json(['id' => $result['id']]);
    }

    
    public function fileDelet(Request $request)
    {
        $id = $request->get('id');
        
        $file = AttachmentModel::where('id',$id)->first()->toArray();
        if(!$file)
        {
            return response()->json(['errCode' => 0, 'errMsg' => '附件没有上传成功！']);
        }
        
        if(is_file($file['url']))
            unlink($file['url']);
        $result = AttachmentModel::destroy($id);
        if (!$result) {
            return response()->json(['errCode' => 0, 'errMsg' => '删除失败！']);
        }
        return response()->json(['errCode' => 1, 'errMsg' => '删除成功！']);
    }

    
    public function weixinNotify()
    {
        
        $arrNotify = \CommonClass::xmlToArray($GLOBALS['HTTP_RAW_POST_DATA']);

        $data = [
            'pay_account' => $arrNotify['buyer_email'],
            'code' => $arrNotify['out_trade_no'],
            'pay_code' => $arrNotify['trade_no'],
            'money' => $arrNotify['total_fee'],
            'task_id' => $arrNotify['task_id']
        ];

        $content = '<xml>
                    <return_code><![CDATA[SUCCESS]]></return_code>
                    <return_msg><![CDATA[OK]]></return_msg>
                    </xml>';

        if ($arrNotify['result_code'] == 'SUCCESS' && $arrNotify['return_code'] = 'SUCCESS') {

            
            
            
            return response($content)->header('Content-Type', 'text/xml');
        }
    }

    
    public function result(Request $request)
    {
        $data = $request->all();
        $data = [
            'pay_account' => $data['buyer_email'],
            'code' => $data['out_trade_no'],
            'pay_code' => $data['trade_no'],
            'money' => $data['total_fee'],
        ];
        $gateway = Omnipay::gateway('alipay');

        $options = [
            'request_params' => $_REQUEST,
        ];
        $response = $gateway->completePurchase($options)->send();

        if ($response->isSuccessful() && $response->isTradeStatusOk()) {
            
            $result = UserDetailModel::recharge($this->user['id'], 2, $data);

            if (!$result) {
                echo '支付失败！';
                return redirect()->back()->withErrors(['errMsg' => '支付失败！']);
            }
            
            $task_id = OrderModel::where('code', $data['code'])->first();

            TaskModel::bounty($data['money'], $task_id['task_id'], $this->user['id'], $data['code'], 2);
            echo '支付成功';
            return redirect()->to('task/' . $task_id['task_id']);
        } else {
            
            echo '支付失败';
            return redirect()->to('task/bounty')->withErrors(['errMsg' => '支付失败！']);
        }
    }

    
    public function notify(Request $request)
    {
        $data = $request->all();
        $data = [
            'pay_account' => $data['buyer_email'],
            'code' => $data['out_trade_no'],
            'pay_code' => $data['trade_no'],
            'money' => $data['total_fee'],
        ];
        $gateway = Omnipay::gateway('alipay');
        $options = [
            'request_params' => $_REQUEST,
        ];
        $response = $gateway->completePurchase($options)->send();

        if ($response->isSuccessful() && $response->isTradeStatusOk()) {
            
            $result = UserDetailModel::recharge($this->user['id'], 2, $data);
            if (!$result) {
                echo '支付失败！';
                return redirect()->back()->withErrors(['errMsg' => '支付失败！']);
            }
            
            $task_id = OrderModel::where('code', $data['code'])->first();

            TaskModel::bounty($data['money'], $task_id['task_id'], $this->user['id'], $data['code'], 2);
            echo '支付成功';
            return redirect()->to('task/' . $task_id['task_id']);
        } else {
            
            return redirect()->to('task/bounty')->withErrors(['errMsg' => '支付失败！']);
        }
    }

    
    public function ajaxcity(Request $request)
    {
        $id = intval($request->get('id'));
        if (!$id) {
            return response()->json(['errMsg' => '参数错误！']);
        }
        $province = DistrictModel::findTree($id);
        
        $area = DistrictModel::findTree($province[0]['id']);
        $data = [
            'province' => $province,
            'area' => $area
        ];
        return response()->json($data);
    }

    
    public function ajaxarea(Request $request)
    {
        $id = intval($request->get('id'));
        if (!$id) {
            return response()->json(['errMsg' => '参数错误！']);
        }
        $area = DistrictModel::findTree($id);
        return response()->json($area);
    }

    
    public function release($id)
    {
        $this->theme->setTitle('发布任务');
        
        $task = TaskModel::where('id', $id)->first();
        if(!$task)
        {
            return redirect()->to('user/unreleasedTasks')->with(['error'=>'非法操作！']);
        }
        
        $category = TaskCateModel::findAll();

        
        $hotCate = TaskCateModel::hotCate(6);
        
        $category_all = TaskCateModel::findByPid([0],['id']);
        $category_all = array_flatten($category_all);
        $category_all = TaskCateModel::findByPid($category_all);
        
        
        $service = ServiceModel::all();
        $task_service = TaskServiceModel::where('task_id', $id)->lists('service_id')->toArray();
        $task_service_ids = array_flatten($task_service);
        
        $task_service_money = ServiceModel::serviceMoney($task_service_ids);


        $province = DistrictModel::findTree(0);
        
        if ($task['region_limit'] == 1) {
            $city = DistrictModel::findTree($task['province']);
            $area = DistrictModel::findTree($task['city']);
        } else {
            $city = DistrictModel::findTree($province[0]['id']);
            $area = DistrictModel::findTree( $city[0]['id']);
        }

        
        $task_attachment = TaskAttachmentModel::where('task_id', $id)->lists('attachment_id')->toArray();
        $task_attachment_ids = array_flatten($task_attachment);
        $task_attachment_data = AttachmentModel::whereIn('id', $task_attachment_ids)->get();
        $domain = \CommonClass::getDomain();
        
        $taskType = [
            /*'xuanshang',*/'zhaobiao'
        ];
        $rewardModel = TaskTypeModel::whereIn('alias',$taskType)->get()->toArray();
        $taskTypeAlias = TaskTypeModel::getTaskTypeAliasById($task['type_id']);
        
        $phone = \CommonClass::getConfig('phone');
        $qq = \CommonClass::getConfig('qq');
        
        $ad = AdTargetModel::getAdInfo('TASKINFO_RIGHT');
        
        $agree = AgreementModel::where('code_name','task_publish')->first();
        $view = [
            'hotcate' => $hotCate,
            'category' => $category,
            'category_all' => $category_all,
            'service' => $service,
            'task' => $task,
            'province' => $province,
            'city' => $city,
            'area' => $area,
            'task_service_ids' => $task_service_ids,
            'task_service_money' => $task_service_money,
            'task_attachment_data' => $task_attachment_data,
            'domain' => $domain,
            'rewardModel'=>$rewardModel,
            'phone'=>$phone,
            'qq'=>$qq,
            'agree' => $agree,
            'ad' => $ad,
            'task_type_alias' => $taskTypeAlias
        ];

        return $this->theme->scope('task.release', $view)->render();
    }

    
    public function checkBounty(Request $request)
    {
        $data = $request->except('_token');
        $begin_at = preg_replace('/([\x80-\xff]*)/i', '', $data['begin_at']);
        
        $task_bounty_max_limit = \CommonClass::getConfig('task_bounty_max_limit');
        $task_bounty_min_limit = \CommonClass::getConfig('task_bounty_min_limit');

        
        if ($task_bounty_min_limit > $data['param']) {
            $data['info'] = '赏金应该大于' . $task_bounty_min_limit . '小于' . $task_bounty_max_limit;
            $data['status'] = 'n';
            return json_encode($data);
        }
        
        if ($task_bounty_max_limit < $data['param'] && $task_bounty_max_limit != 0) {
            $data['info'] = '赏金应该大于' . $task_bounty_min_limit . '小于' . $task_bounty_max_limit;
            $data['status'] = 'n';
            return json_encode($data);
        }

        
        $task_delivery_limit_time = \CommonClass::getConfig('task_delivery_limit_time');
        $task_delivery_limit_time = json_decode($task_delivery_limit_time, true);
        $task_delivery_limit_time_key = array_keys($task_delivery_limit_time);

        $task_delivery_limit_time_key = \CommonClass::get_rand($task_delivery_limit_time_key, $data['param']);
        if(in_array($task_delivery_limit_time_key,array_keys($task_delivery_limit_time))){
            $task_delivery_limit_time = $task_delivery_limit_time[$task_delivery_limit_time_key];
        }else{
            $task_delivery_limit_time = 100;
        }


        $data['status'] = 'y';
        $data['info'] = '您当前的发布的任务金额是' . $data['param'] . ',截稿时间是' . $task_delivery_limit_time . '天';
        $data['deadline'] = date('Y年m月d日',strtotime($begin_at)+$task_delivery_limit_time*24*3600);

        return json_encode($data);
    }

    
    public function checkDeadline(Request $request)
    {
        $data = $request->except('_token');
        $delivery_deadline = preg_replace('/([\x80-\xff]*)/i', '', $data['delivery_deadline']);
        $begin_at = preg_replace('/([\x80-\xff]*)/i', '', $data['begin_at']);
        
        if (empty($data['param'])) {
            return json_encode(['info' => '请先填写任务赏金', 'status' => 'n']);
        }
        
        if (empty($data['begin_at'])) {
            return json_encode(['info' => '请先填写任务开始时间', 'status' => 'n']);
        }
        
        if (strtotime($data['begin_at'])>=strtotime(date('Y-m-d',time()))) {
            return json_encode(['info' => '开始时间不能在今天之前', 'status' => 'n']);
        }
        
        if (empty($data['delivery_deadline'])) {
            return json_encode(['info' => '请填写任务截稿时间', 'status' => 'n']);
        }
        
        if(date('Ymd',strtotime($delivery_deadline))==date('Ymd',strtotime($begin_at)))
        {
            return json_encode(['info' => '接任务时间最少一天', 'status' => 'n','begin_at'=>$data['begin_at'],'delivery_deadline'=>date('Ymd',strtotime($data['delivery_deadline']))]);
        }
        
        $task_bounty_max_limit = \CommonClass::getConfig('task_bounty_max_limit');
        $task_bounty_min_limit = \CommonClass::getConfig('task_bounty_min_limit');
        
        $task_delivery_limit_time = \CommonClass::getConfig('task_delivery_limit_time');
        $task_delivery_limit_time = json_decode($task_delivery_limit_time, true);
        $task_delivery_limit_time_key = array_keys($task_delivery_limit_time);
        $task_delivery_limit_time_key = \CommonClass::get_rand($task_delivery_limit_time_key, $data['param']);
        $task_delivery_limit_time = $task_delivery_limit_time[$task_delivery_limit_time_key];
        
        if ($task_bounty_min_limit > $data['param']) {
            $info = '赏金应该大于' . $task_bounty_min_limit . '小于' . $task_bounty_max_limit;
            return json_encode(['info' => $info, 'status' => 'n']);
        }
        
        if ($task_bounty_max_limit < $data['param'] && $task_bounty_max_limit != 0) {
            $info = '赏金应该大于' . $task_bounty_min_limit . '小于' . $task_bounty_max_limit;
            return json_encode(['info' => $info, 'status' => 'n']);
        }
        
        $delivery_deadline = strtotime($delivery_deadline);
        $task_delivery_limit_time = $task_delivery_limit_time * 24 * 3600;
        $begin_at = strtotime($begin_at);
        
        if ($begin_at > $delivery_deadline) {
            $info = '截稿时间不能小于开始时间';
            return json_encode(['info' => $info, 'status' => 'n']);
        }
        if (($begin_at + $task_delivery_limit_time) < $delivery_deadline) {
            $info = '当前截稿时间最晚可设置为' . date('Y-m-d', ($begin_at + $task_delivery_limit_time));
            return json_encode(['info' => $info, 'status' => 'n']);
        }
        $info = '当前截稿时间最晚可设置为' . date('Y-m-d', ($begin_at + $task_delivery_limit_time));
        $status = 'y';
        $data = array(
            'info' => $info,
            'status' => $status
        );
        return json_encode($data);

    }


    
    public function checkDeadlineByBid(Request $request)
    {
        $data = $request->except('_token');
        $delivery_deadline = preg_replace('/([\x80-\xff]*)/i', '', $data['delivery_deadline']);
        $begin_at = preg_replace('/([\x80-\xff]*)/i', '', $data['begin_at']);
        
        if (empty($data['begin_at'])) {
            return json_encode(['info' => '请先填写任务开始时间', 'status' => 'n']);
        }
        
        if (strtotime($data['begin_at'])>=strtotime(date('Y-m-d',time()))) {
            return json_encode(['info' => '开始时间不能在今天之前', 'status' => 'n']);
        }
        
        if (empty($data['delivery_deadline'])) {
            return json_encode(['info' => '请填写任务截稿时间', 'status' => 'n']);
        }
        
        if(date('Ymd',strtotime($delivery_deadline))==date('Ymd',strtotime($begin_at))) {
            return json_encode(['info' => '接任务时间最少一天', 'status' => 'n','begin_at'=>$data['begin_at'],'delivery_deadline'=>date('Ymd',strtotime($data['delivery_deadline']))]);
        }
        
        if (isset($data['param']) && !empty($data['param'])) {
            $task_bounty_max_limit = \CommonClass::getConfig('bid_bounty_limit');
            $task_bounty_min_limit = \CommonClass::getConfig('bid_bounty_min_limit');
            
            if ($task_bounty_min_limit > $data['param']) {
                $info = '赏金应该大于' . $task_bounty_min_limit . '小于' . $task_bounty_max_limit;
                return json_encode(['info' => $info, 'status' => 'n']);
            }
            
            if ($task_bounty_max_limit < $data['param'] && $task_bounty_max_limit != 0) {
                $info = '赏金应该大于' . $task_bounty_min_limit . '小于' . $task_bounty_max_limit;
                return json_encode(['info' => $info, 'status' => 'n']);
            }
        }

        
        $delivery_deadline = strtotime($delivery_deadline);
        $begin_at = strtotime($begin_at);
        $max_limit_delivery = \CommonClass::getConfig('bid_delivery_max');
        $test = $max_limit_delivery;
        $max_limit_delivery = $max_limit_delivery * 24 * 3600;
        $deadlineMax = $begin_at + $max_limit_delivery;
        
        if ($begin_at > $delivery_deadline) {
            $info = '截稿时间不能小于开始时间';
            return json_encode(['info' => $info, 'status' => 'n']);
        }
        if ($deadlineMax < $delivery_deadline) {
            $info = '当前截稿时间最晚可设置为' . date('Y-m-d', $deadlineMax);
            return json_encode(['info' => $info, 'status' => 'n' ,'as'=>$test]);
        }
        $info = '当前截稿时间最晚可设置为' . date('Y-m-d', $deadlineMax);
        $status = 'y';
        $data = array(
            'info' => $info,
            'status' => $status
        );
        return json_encode($data);

    }

    public function imgupload(Request $request)
    {
        $data = $request->all();
        dd($data);
    }

    
    public function collectionTask($taskId)
    {
        
        $userId = $this->user['id'];
        if ($userId && $taskId) {
            
            $focus = TaskFocusModel::where('uid',$userId)->where('task_id',$taskId)->first();
            if($focus) {
                $route = '/task';
                $msg = '该任务已经收藏过';
            }else{
                $focusArr = array(
                    'uid' => $userId,
                    'task_id' => $taskId,
                    'created_at' => date('Y-m-d H:i:s', time())
                );
                $res = TaskFocusModel::create($focusArr);
                if ($res) {
                    $route = '/task';
                    $msg = '收藏成功';

                } else {
                    $route = '/task';
                    $msg = '收藏失败';
                }
            }
        } else {
            $route = '/task';
            $msg = '没有登录，不能收藏';
        }
        return redirect($route)->with(array('message' => $msg));
    }

    
    public function postCollectionTask(Request $request)
    {
        
        $userId = $this->user['id'];
        if(!empty($userId)){
            $taskId = $request->get('task_id');
            $type = $request->get('type');
            switch($type){
                
                case 1 :
                    
                    $focus = TaskFocusModel::where('uid',$userId)->where('task_id',$taskId)->first();
                    if($focus) {
                        $data = array(
                            'code' => 2,
                            'msg' => '该任务已经收藏过'
                        );
                    }else{
                        $focusArr = array(
                            'uid' => $userId,
                            'task_id' => $taskId,
                            'created_at' => date('Y-m-d H:i:s', time())
                        );
                        $res = TaskFocusModel::create($focusArr);
                        if ($res) {
                            $data = array(
                                'code' => 1,
                                'msg' => '收藏成功'
                            );

                        } else {
                            $data = array(
                                'code' => 2,
                                'msg' => '收藏失败'
                            );
                        }
                    }
                    break;
                
                case 2 :
                    
                    $focus = TaskFocusModel::where('uid',$userId)->where('task_id',$taskId)->first();
                    if(empty($focus)) {
                        $data = array(
                            'code' => 2,
                            'msg' => '该任务已经取消收藏'
                        );
                    }else{
                        $res = TaskFocusModel::where('uid',$userId)->where('task_id',$taskId)->delete();
                        if ($res) {
                            $data = array(
                                'code' => 1,
                                'msg' => '取消成功'
                            );

                        } else {
                            $data = array(
                                'code' => 2,
                                'msg' => '取消失败'
                            );
                        }
                    }
                    break;
            }
        }else{
            $data = array(
                'code' => 0,
                'msg' => '没有登录，不能收藏'
            );
        }
        return response()->json($data);
    }

    public function checkDesc(Request $request)
    {
        $data = $request->except('_token');
        dd($data);
    }

    
    public function taskSuccess($id)
    {
        $id = intval($id);
        
        $task = TaskModel::where('id',$id)->first();

        $taskTypeAlias = 'xuanshang';
        $taskType = TaskTypeModel::find($task['type_id']);
        if(!empty($taskType)){
            $taskTypeAlias = $taskType['alias'];
        }

        switch($taskTypeAlias){
            case 'xuanshang' :
                if($task['status']!=2){
                    return redirect()->back()->with(['error'=>'数据错误，当前任务不处于等待审核状态！']);
                }
                break;

            case 'zhaobiao' :
                if($task['status'] == 3){ 
                    return redirect('/task/'.$id);
                }
                break;

            default:
                break;
        }


        $qq = \CommonClass::getConfig('qq');
        $view = [
            'id'=>$id,
            'qq'=>$qq,
        ];

        return $this->theme->scope('task.tasksuccess',$view)->render();
    }


    
    public function buyServiceTaskBid($id)
    {
        $this->theme->setTitle('招标任务购买增值服务');
        
        $task = TaskModel::findById($id);

        
        $user_money = UserDetailModel::where(['uid' => $this->user['id']])->first();
        $user_money = $user_money['balance'];

        
        $service = TaskServiceModel::select('task_service.service_id')
            ->where('task_id', '=', $id)->get()->toArray();
        $service = array_flatten($service);
        $service_money = ServiceModel::serviceMoney($service);
        
        $balance_pay = false;
        if ($user_money > $service_money) {
            $balance_pay = true;
        }

        
        $bank = BankAuthModel::where('uid', '=', $id)->where('status', '=', 4)->get();
        
        $payConfig = ConfigModel::getConfigByType('thirdpay');
        $view = [
            'task' => $task,
            'bank' => $bank,
            'service_money' => $service_money,
            'id' => $id,
            'user_money' => $user_money,
            'balance_pay' => $balance_pay,
            'payConfig' => $payConfig
        ];
        return $this->theme->scope('task.bid.buyservice', $view)->render();
    }


    
    public function postBuyServiceTaskBid(BountyRequest $request)
    {
        $data = $request->except('_token');
        $data['id'] = intval($data['id']);
        
        $task = TaskModel::findById($data['id']);

        
        if ($task['uid'] != $this->user['id'] || $task['bounty_status'] != 0) {
            return redirect()->to('/task/' . $task['id'])->with('error', '非法操作！');
        }

        
        $balance = UserDetailModel::where(['uid' => $this->user['id']])->first();
        $balance = (float)$balance['balance'];

        
        $service = TaskServiceModel::select('task_service.service_id')
            ->where('task_id', '=', $data['id'])->get()->toArray();
        $service = array_flatten($service);
        $money = ServiceModel::serviceMoney($service);
        
        $is_ordered = OrderModel::buyServicebyTaskBid($this->user['id'], $money, $task['id']);
        if (!$is_ordered) {
            return redirect()->back()->with(['error' => '任务发布失败']);
        }

        
        if ($balance >= $money && $data['pay_canel'] == 0)
        {
            
            $password = UserModel::encryptPassword($data['password'], $this->user['salt']);
            if ($password != $this->user['alternate_password']) {
                return redirect()->back()->with(['error' => '您的支付密码不正确']);
            }
            
            $result = TaskModel::buyServiceTaskBid($money, $data['id'], $this->user['id'], $is_ordered->code);
            if (!$result) return redirect()->back()->with(['error' => '赏金托管失败！']);
            
            $task = TaskModel::where('id',$data['id'])->first();
            if($task['status'] == 3){
                $url = 'task/'.$data['id'];
            }elseif($task['status'] == 1){
                $url = 'task/tasksuccess/'.$data['id'];
            }
            return redirect()->to($url);
        } else if (isset($data['pay_type']) && $data['pay_canel'] == 1) {
            
            if ($data['pay_type'] == 1) {
                $config = ConfigModel::getPayConfig('alipay');
                $objOminipay = Omnipay::gateway('alipay');
                $objOminipay->setPartner($config['partner']);
                $objOminipay->setKey($config['key']);
                $objOminipay->setSellerEmail($config['sellerEmail']);
                $siteUrl = \CommonClass::getConfig('site_url');
                $objOminipay->setReturnUrl($siteUrl . '/order/pay/alipay/return');
                $objOminipay->setNotifyUrl($siteUrl . '/order/pay/alipay/notify');

                $response = Omnipay::purchase([
                    'out_trade_no' => $is_ordered->code, 
                    'subject' => \CommonClass::getConfig('site_name'), 
                    'total_fee' => $money, 
                ])->send();
                $response->redirect();
            } else if ($data['pay_type'] == 2) {
                $config = ConfigModel::getPayConfig('wechatpay');
                $wechat = Omnipay::gateway('wechat');
                $wechat->setAppId($config['appId']);
                $wechat->setMchId($config['mchId']);
                $wechat->setAppKey($config['appKey']);
                $out_trade_no = $is_ordered->code;
                $params = array(
                    'out_trade_no' => $is_ordered->code, 
                    'notify_url' => \CommonClass::getDomain() . '/order/pay/wechat/notify?out_trade_no=' . $out_trade_no . '&task_id=' . $data['id'], 
                    'body' => \CommonClass::getConfig('site_name') . '余额充值', 
                    'total_fee' => $money, 
                    'fee_type' => 'CNY', 
                );
                $response = $wechat->purchase($params)->send();

                $img = QrCode::size('280')->generate($response->getRedirectUrl());

                $view = array(
                    'cash'=>$money,
                    'img' => $img
                );
                return $this->theme->scope('task.wechatpay', $view)->render();
            } else if ($data['pay_type'] == 3) {
                dd('银联支付！');
            }
        } else if (isset($data['account']) && $data['pay_canel'] == 2) {
            dd('银行卡支付！');
        } else
        {
            return redirect()->back()->with(['error' => '请选择一种支付方式']);
        }
    }

    
    public function bidBounty($id)
    {
        $this->theme->setTitle('赏金托管');

        $task = TaskModel::find($id);


        if ($task['uid'] != $this->user['id'] || $task['bounty_status'] != 0) {
            return redirect()->to('/task/'.$id)->with(['error' => '非法操作！']);
        }

        $user_money = UserDetailModel::where(['uid' => $this->user['id']])->first();
        $user_money = $user_money['balance'];


        $balance_pay = false;
        if ($user_money > $task['bounty']) {
            $balance_pay = true;
        }


        $bank = BankAuthModel::where('uid', '=', $id)->where('status', '=', 4)->get();

        $payConfig = ConfigModel::getConfigByType('thirdpay');
        $view = [
            'task' => $task,
            'bank' => $bank,
            'id' => $id,
            'user_money' => $user_money,
            'balance_pay' => $balance_pay,
            'payConfig' => $payConfig
        ];
        return $this->theme->scope('task.bid.bounty', $view)->render();
    }

    public function bidBountyUpdate(BountyRequest $request)
    {
        $data = $request->except('_token');
        $data['id'] = intval($data['id']);

        $task = TaskModel::findById($data['id']);


        if ($task['uid'] != $this->user['id'] || $task['bounty_status'] == 1) {
            return redirect()->to('/task/' . $task['id'])->with('error', '非法操作！');
        }


        $balance = UserDetailModel::where(['uid' => $this->user['id']])->first();
        $balance = (float)$balance['balance'];

        $money = $task['bounty'];

        $is_ordered = OrderModel::bountyOrderByTaskBid($this->user['id'], $money, $task['id']);

        if (!$is_ordered) return redirect()->back()->with(['error' => '任务托管失败']);


        if ($balance >= $money && $data['pay_canel'] == 0)
        {

            $password = UserModel::encryptPassword($data['password'], $this->user['salt']);
            if ($password != $this->user['alternate_password']) {
                return redirect()->back()->with(['error' => '您的支付密码不正确']);
            }

            $result = TaskModel::bidBounty($money, $data['id'], $this->user['id'], $is_ordered->code);
            if (!$result){
                return redirect()->back()->with(['error' => '赏金托管失败！']);
            }
            $url = 'task/'.$data['id'];
            return redirect()->to($url);
        } else if (isset($data['pay_type']) && $data['pay_canel'] == 1) {

            if ($data['pay_type'] == 1) {
                $config = ConfigModel::getPayConfig('alipay');
                $objOminipay = Omnipay::gateway('alipay');
                $objOminipay->setPartner($config['partner']);
                $objOminipay->setKey($config['key']);
                $objOminipay->setSellerEmail($config['sellerEmail']);
                $siteUrl = \CommonClass::getConfig('site_url');
                $objOminipay->setReturnUrl($siteUrl . '/order/pay/alipay/return');
                $objOminipay->setNotifyUrl($siteUrl . '/order/pay/alipay/notify');

                $response = Omnipay::purchase([
                    'out_trade_no' => $is_ordered->code,
                    'subject' => \CommonClass::getConfig('site_name'),
                    'total_fee' => $money,
                ])->send();
                $response->redirect();
            } else if ($data['pay_type'] == 2) {
                $config = ConfigModel::getPayConfig('wechatpay');
                $wechat = Omnipay::gateway('wechat');
                $wechat->setAppId($config['appId']);
                $wechat->setMchId($config['mchId']);
                $wechat->setAppKey($config['appKey']);
                $out_trade_no = $is_ordered->code;
                $params = array(
                    'out_trade_no' => $is_ordered->code,
                    'notify_url' => \CommonClass::getDomain() . '/order/pay/wechat/notify?out_trade_no=' . $out_trade_no . '&task_id=' . $data['id'],
                    'body' => \CommonClass::getConfig('site_name') . '余额充值',
                    'total_fee' => $money,
                    'fee_type' => 'CNY',
                );
                $response = $wechat->purchase($params)->send();

                $img = QrCode::size('280')->generate($response->getRedirectUrl());

                $view = array(
                    'cash'=>$money,
                    'img' => $img
                );
                return $this->theme->scope('task.wechatpay', $view)->render();
            } else if ($data['pay_type'] == 3) {
                dd('银联支付！');
            }
        } else if (isset($data['account']) && $data['pay_canel'] == 2) {
            dd('银行卡支付！');
        } else
        {
            return redirect()->back()->with(['error' => '请选择一种支付方式']);
        }

    }
    public function submitExperts(Request $request)
    {
        $data['detail']=$request->detail;
        $data['status']=1;
        $data['time']=date('Y-m-d H:i:s',time());
        $tid=$request->task_id;
        DB::table('experts_task')->where('task_id',$tid)->update($data);
        DB::table('task')->whereId($tid)->update(['status'=>11]);
        header('Location:'.url('/task',$tid));
    }
    //add by xl 签订合同
    public function signContract($taskId,$status){
        $this->theme->setTitle('签订合同');

        $task = TaskModel::find($taskId);

        if ($task['uid'] != $this->user['id'] || $task['bounty_status'] != 0) {
            return redirect()->to('/task/'.$taskId)->with(['error' => '非法操作！']);
        }

        $view = [
            'task'   => $task,
            'id'     => $taskId,
            'status' => $status,
        ];
        return $this->theme->scope('task.contract', $view)->render();

    }
    public function signContractUpdate(ContractRequest $request){
        $data = $request->except('_token');
        $task_id = intval($data['task_id']);
        $data['created_at'] = date('Y-m-d H:i:s', time());

        $task = TaskModel::findById($task_id);
        if ($task['uid'] != $this->user['id'] ) {
            return redirect()->to('/task/' . $task_id)->with('error', '非法操作！');
        }
        $money = $data['money'];
        if ($money <= 0 || !is_numeric($money))  return redirect()->to('/task/signContract/' . $task_id.'/'.$data['status'])->with('error', '合同金额必须大于零！');
        //查找该任务中标者即为被签订合同者
        $worker =WorkModel::where('task_id',$task_id)->where('status',1)->first();

        if (!empty($data['file_id'])) {

            $file_able_ids = AttachmentModel::fileAble($data['file_id']);
            $file_able_ids = array_flatten($file_able_ids);
            if(isset($task_id)){
                \DB::table('task_contract')->where('task_id',$task_id)->delete();
               // TaskAttachmentModel::where('task_id',$data['task_id'])->delete();
            }
            /*foreach ($file_able_ids as $v) {
                $attachment_data = [
                    'task_id' => $result['id'],
                    'attachment_id' => $v,
                    'created_at' => date('Y-m-d H:i:s', time()),
                ];

                TaskAttachmentModel::create($attachment_data);
            }*/

            $attachment_data = implode(',',$file_able_ids);
            $contract_data = [
                'task_id'       => $task_id,
                'attachment_id' => $attachment_data,
                'money'         => $money,
                'uid'           => $this->user['id'],
                'withwho'       => $worker['uid'],
                'created_at'    => date('Y-m-d H:i:s', time()),
            ];
            $insert =  \DB::table('task_contract')->insert($contract_data);
            if($insert){
                $changes= TaskModel::where('id', $task_id)->update(['status' => $data['status'],'updated_at' => date('Y-m-d H:i:s'),'publicity_at'=>date('Y-m-d H:i:s',time())]);
                if($changes){
                    return redirect()->to('/task/'.$task_id);
                }else{
                    return redirect()->to('/task/'.$task_id)->with(['message' => '操作失败']);
                }
            }else{
                return redirect()->to('/task/'.$task_id)->with(['message' => '操作失败']);
            }

            $attachmentModel = new AttachmentModel();
            $attachmentModel->statusChange($file_able_ids);
        }else{
            return redirect()->to('/task/signContract/' . $task['id'].'/'.$data['status'])->with('error', '请上传合同附件！');
        }

        $url = 'task/'.$data['task_id'];
        return redirect()->to($url);
    }
    //add by xl 增加线下付款
    public function offlinePayment($id)
    {
        $this->theme->setTitle('线下付款');

        $task = TaskModel::find($id);

        if ($task['uid'] != $this->user['id'] || $task['bounty_status'] != 0) {
            return redirect()->to('/task/'.$id)->with(['error' => '非法操作！']);
        }
        $money = $task['bounty'];
        $is_ordered = OrderModel::offlineOrderByTaskBid($this->user['id'], $money, $task['id']);

        $result = TaskModel::offlinePay(0, $task['id'], $this->user['id'], $is_ordered->code);
        if (!$result){
            return redirect()->back()->with(['error' => '线下付款失败！']);
        }else{
            $url = 'task/'.$task['id'];
            return redirect()->to($url);
        }

    }
    /*
     * 仲裁费*/
    public function arbitrationBounty($id)
    {
        $this->theme->setTitle('支付仲裁费');

        $task = TaskModel::find($id);
        $task['bounty'] = 3000;

//        if ($task['uid'] != $this->user['id'] || $task['bounty_status'] != 0) {
//            return redirect()->to('/task/'.$id)->with(['error' => '非法操作！']);
//        }

        $user_money = UserDetailModel::where(['uid' => $this->user['id']])->first();
        $user_money = $user_money['balance'];

        $balance_pay = false;
        if ($user_money > $task['bounty']) {
            $balance_pay = true;
        }

        $bank = BankAuthModel::where('uid', '=', $id)->where('status', '=', 4)->get();

        $payConfig = ConfigModel::getConfigByType('thirdpay');
        $view = [
            'task' => $task,
            'bank' => $bank,
            'id' => $id,
            'user_money' => $user_money,
            'balance_pay' => $balance_pay,
            'payConfig' => $payConfig
        ];
        return $this->theme->scope('task.bid.arbitrationbounty', $view)->render();
    }
    /*支付仲裁费*/
    public function arbitrationBountyUpdate(BountyRequest $request)
    {
        $data = $request->except('_token');
        $data['id'] = intval($data['id']);

        $task = TaskModel::findById($data['id']);


//        if ($task['uid'] != $this->user['id'] || $task['bounty_status'] == 1) {
//            return redirect()->to('/task/' . $task['id'])->with('error', '非法操作！');
//        }


        $balance = UserDetailModel::where(['uid' => $this->user['id']])->first();
        $balance = (float)$balance['balance'];

        $money = $task['bounty'] = 3000;

        $is_ordered = OrderModel::bountyOrderByTaskBid($this->user['id'], $money, $task['id']);

        if (!$is_ordered) return redirect()->back()->with(['error' => '任务托管失败']);


        if ($balance >= $money && $data['pay_canel'] == 0)
        {

            $password = UserModel::encryptPassword($data['password'], $this->user['salt']);
            if ($password != $this->user['alternate_password']) {
                return redirect()->back()->with(['error' => '您的支付密码不正确']);
            }

            $result = TaskModel::arbitrationBounty($money, $data['id'], $this->user['id'], $is_ordered->code);
            if (!$result){
                return redirect()->back()->with(['error' => '赏金托管失败！']);
            }
            $url = 'task/'.$data['id'];
            return redirect()->to($url);
        } else if (isset($data['pay_type']) && $data['pay_canel'] == 1) {

            if ($data['pay_type'] == 1) {
                $config = ConfigModel::getPayConfig('alipay');
                $objOminipay = Omnipay::gateway('alipay');
                $objOminipay->setPartner($config['partner']);
                $objOminipay->setKey($config['key']);
                $objOminipay->setSellerEmail($config['sellerEmail']);
                $siteUrl = \CommonClass::getConfig('site_url');
                $objOminipay->setReturnUrl($siteUrl . '/order/pay/alipay/return');
                $objOminipay->setNotifyUrl($siteUrl . '/order/pay/alipay/notify');

                $response = Omnipay::purchase([
                    'out_trade_no' => $is_ordered->code,
                    'subject' => \CommonClass::getConfig('site_name'),
                    'total_fee' => $money,
                ])->send();
                $response->redirect();
            } else if ($data['pay_type'] == 2) {
                $config = ConfigModel::getPayConfig('wechatpay');
                $wechat = Omnipay::gateway('wechat');
                $wechat->setAppId($config['appId']);
                $wechat->setMchId($config['mchId']);
                $wechat->setAppKey($config['appKey']);
                $out_trade_no = $is_ordered->code;
                $params = array(
                    'out_trade_no' => $is_ordered->code,
                    'notify_url' => \CommonClass::getDomain() . '/order/pay/wechat/notify?out_trade_no=' . $out_trade_no . '&task_id=' . $data['id'],
                    'body' => \CommonClass::getConfig('site_name') . '余额充值',
                    'total_fee' => $money,
                    'fee_type' => 'CNY',
                );
                $response = $wechat->purchase($params)->send();

                $img = QrCode::size('280')->generate($response->getRedirectUrl());

                $view = array(
                    'cash'=>$money,
                    'img' => $img
                );
                return $this->theme->scope('task.wechatpay', $view)->render();
            } else if ($data['pay_type'] == 3) {
                dd('银联支付！');
            }
        } else if (isset($data['account']) && $data['pay_canel'] == 2) {
            dd('银行卡支付！');
        } else
        {
            return redirect()->back()->with(['error' => '请选择一种支付方式']);
        }
    }


}
