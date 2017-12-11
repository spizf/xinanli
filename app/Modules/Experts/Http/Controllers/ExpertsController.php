<?php
namespace App\Modules\Experts\Http\Controllers;

use App\Http\Controllers\IndexController as BasicIndexController;
use App\Http\Requests;
use App\Modules\Manage\Model\ConfigModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Theme;
use QrCode;
use Cache;
use App\Modules\Bre\Http\Controllers\ServiceController;

class ExpertsController extends BasicIndexController
{
    public function __construct()
    {
        parent::__construct();
        $this->user = Auth::user();
        $this->initTheme('main');
    }
    public function expertsList($cate_id=null,$addr_id=null){
        $num=12;
        $seoConfig = ConfigModel::getConfigByType('seo');
        if(!empty($seoConfig['seo_task']) && is_array($seoConfig['seo_task'])){
            $this->theme->setTitle($seoConfig['seo_task']['title']);
            $this->theme->set('keywords',$seoConfig['seo_task']['keywords']);
            $this->theme->set('description',$seoConfig['seo_task']['description']);
        }else{
            $this->theme->setTitle('仲裁专家列表');
        }
        $data['ad']=DB::table('ad_target')
            ->select('ad.*')
            ->join('ad','ad_target.target_id','=','ad.target_id')
            ->where('ad_target.code','EXPERTS_BOTTOM')
            ->first();
        if($cate_id||$addr_id){
            if(!$cate_id&&!$addr_id){
                $data['experts'] = DB::table('experts')->orderBy('recommend', 'desc')->paginate($num);
                $data['cate_id'] = 0;
                $data['addr_id'] = 0;
            }elseif($cate_id&&!$addr_id){
                if($cate_id=='ask_num'||$cate_id=='satisfaction'){
                    $data['experts'] = DB::table('experts')->orderBy($cate_id, 'desc')->paginate($num);
                    $data['sort']=$cate_id;
                }else {
                    $data['experts'] = DB::table('experts')
                        ->select('experts.*', 'position.position')
                        ->leftJoin('position', 'experts.position', '=', 'position.id')
                        ->where('cate', 'like', $cate_id . ',%')
                        ->orWhere('cate', 'like', '%,' . $cate_id . ',%')
                        ->orWhere('cate', 'like', '%,' . $cate_id)
                        ->orWhere('cate', '=', $cate_id)
                        ->orderBy('recommend', 'desc')
                        ->paginate($num);
                    $data['cate_id'] = intval($cate_id);
                    $data['addr_id'] = 0;
                }
            }elseif($addr_id&&!$cate_id){
                $data['experts']=DB::table('experts')
                    ->select('experts.*','position.position')
                    ->leftJoin('position','experts.position','=','position.id')
                    ->where('addr','like',$addr_id.'-%')
                    ->orWhere('addr','like','%-'.$addr_id.'-%')
                    ->orWhere('addr','=',$addr_id)
                    ->orderBy('recommend','desc')
                    ->paginate($num);
                $data['addr_id'] = $addr_id;
                $data['cate_id'] = 0;
            }else{
                $data['experts']=DB::table('experts')
                    ->select('experts.*','position.position')
                    ->leftJoin('position','experts.position','=','position.id')
                    ->where('addr','like',$addr_id.'-%')
                    ->orWhere('addr','like','%-'.$addr_id.'-%')
                    ->orWhere('addr','=',$addr_id)
                    ->where('cate','like',$cate_id.',%')
                    ->orWhere('cate','like','%,'.$cate_id.',%')
                    ->orWhere('cate','like','%,'.$cate_id)
                    ->orWhere('cate','=',$cate_id)
                    ->orderBy('recommend','desc')
                    ->paginate($num);
                $data['addr_id'] = $addr_id;
                $data['cate_id'] = $cate_id;
            }
        }else{
            $data['experts']=DB::table('experts')
                ->select('experts.*','position.position')
                ->leftJoin('position','experts.position','=','position.id')->orderBy('recommend','desc')->paginate($num);
        }
        $data['cate']=DB::table('cate as a')->select('a.*')
            ->join('cate as b','a.pid','=','b.id')
            ->where('a.pid','!=',0)
            ->orderBy('sort','desc')
            ->limit(15)->get();
        $data['district']=DB::table('district')
            ->where('upid','0')
            ->orderBy('experts_num','desc')
            ->limit(15)
            ->get();
        $data['count']=DB::table('experts')->select(DB::raw('count(*) as total'))->first();//dd($data);
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
                    $data['experts'][$k]->cate=explode(',',$vv);
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
        return $this->theme->scope('experts.experts_list',$data)->render();
    }
    public function expertsDetail($id){
        $seoConfig = ConfigModel::getConfigByType('seo');
        if(!empty($seoConfig['seo_task']) && is_array($seoConfig['seo_task'])){
            $this->theme->setTitle($seoConfig['seo_task']['title']);
            $this->theme->set('keywords',$seoConfig['seo_task']['keywords']);
            $this->theme->set('description',$seoConfig['seo_task']['description']);
        }else{
            $this->theme->setTitle('仲裁专家详情');
        }
        $expert['ad']=DB::table('ad_target')
            ->select('ad.*')
            ->join('ad','ad_target.target_id','=','ad.target_id')
            ->where('ad_target.code','EXPERTS_BOTTOM')
            ->first();
        $expert['data']=DB::table('experts')
            ->select('experts.*','position.position')
            ->leftJoin('position','experts.position','=','position.id')
            ->where('experts.id',$id)
            ->first();
        $expert['data']->user=DB::table('users')->where('name',$expert['data']->name)->first();
        $expert['experts']=DB::table('experts')
            ->select('experts.*','position.position')
            ->leftJoin('position','experts.position','=','position.id')
            ->where('experts.id','!=',$id)
            ->orderBy('experts.recommend','desc')
            ->limit(10)
            ->get();
        foreach($expert['experts'] as $k=>$v){
            foreach($v as $kk=>$vv) {
                $expert['experts'][$k]->user=DB::table('users')->where('name',$v->name)->first();
                if($kk=='addr') {
                    $expert['experts'][$k]->addr=explode('-',$vv);
                    foreach($expert['experts'][$k]->addr as $key=>$item){
                        $distirct=DB::table('district')->whereId($item)->first();
                        if($distirct) {
                            $expert['experts'][$k]->addr[$key] = $distirct->name;
                        }
                    }
                }
                if($kk=='cate') {
                    $expert['experts'][$k]->cate=explode(',',$vv);
                    foreach($expert['experts'][$k]->cate as $key=>$item){
                        $distirct=DB::table('cate')->whereId($item)->first();
                        if($distirct) {
                            $expert['experts'][$k]->cate[$key] = $distirct->name;
                        }
                    }
                }
            }
            $expert['id'][]=$v->id;
        }
        $expert['data']->addr=explode('-',$expert['data']->addr);
        foreach($expert['data']->addr as $key=>$item){
            $distirct=DB::table('district')->whereId($item)->first();
            if($distirct) {
                $expert['data']->addr[$key] = $distirct->name;
            }
        }
        $expert['data']->cate=explode(',',$expert['data']->cate);
        foreach($expert['data']->cate as $key=>$item){
            $distirct=DB::table('cate')->whereId($item)->first();
            if($distirct) {
                $expert['data']->cate[$key] = $distirct->name;
            }
        }
        $expert['work']=DB::table('experts_work')
            ->select('experts_work.*','position.position')
            ->leftJoin('position','experts_work.position','=','position.id')
            ->where('eid',$id)
            ->first();
        if($expert['work']) {
            $expert['work']->start_time = date('Y年m月', strtotime($expert['work']->start_time));
            $expert['work']->end_time = date('Y年m月', strtotime($expert['work']->end_time)) == '1970年01月' ? '至今' : date('Y年m月', strtotime($expert['work']->end_time));
            $expert['work']->time = $expert['work']->start_time . '-' . $expert['work']->end_time;
        }
        return $this->theme->scope('experts.experts_detail',$expert)->render();
    }
    function sentMessageToExperts($name){
        $user=DB::table('users')->where('name',$name)->first();
        $id=$user->id;//im/addAttention
        $bre=new ServiceController();
        \request()->focus_uid=$id;
        return $bre->ajaxAdd(request());
    }
}
