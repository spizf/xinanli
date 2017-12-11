<?php
/**
 * Created by PhpStorm.
 * User: v_ypshe
 * Date: 2017/11/27
 * Time: 10:07
 */

namespace App\Modules\Manage\Http\Controllers;

use App\Http\Controllers\ManageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FieldController extends ManageController
{
    public function __construct()
    {
        parent::__construct();

        $this->initTheme('manage');
        $this->theme->setTitle('行业管理');
        $this->theme->set('manageType', 'field');
    }

    public function field(){
        $list['list']=[];
        $data['category_data']=DB::table('field')->where('pid',0)->get();
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
}