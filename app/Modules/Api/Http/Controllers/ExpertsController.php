<?php


namespace App\Modules\Api\Http\Controllers;

use App\Http\Controllers\ApiBaseController;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use DB;

class ExpertsController extends ApiBaseController
{
    protected $uid;

    public function __construct(Request $request)
    {

    }

    public function getExperts($id)
    {
        $expert_task = DB::table('experts_task')->where('task_id', $id)->first();
        if ($expert_task) {
            $expert['data'] = DB::table('experts')
                ->select('experts.*', 'position.position as position')
                ->leftJoin('position', 'experts.position', '=', 'position.id')
                ->where('experts.id', $expert_task->experts_id)
                ->first();
            $result = [];
//        id					string	  推荐专家ID
//        nickName			string	专家昵称
//        place				string	职位
//        imageUrl			string	专家头像的URL
//        preference			string	工作年限及城市
//        recommendationRate	string	推荐指数
//        pleased				string	满意度
//        enquiry				tring	咨询量
//        friendUid			string	对方的 uid   发送临时消息使用
            if ($expert['data']) {
                $expert['data']->addr = explode('-', $expert['data']->addr);
                foreach ($expert['data']->addr as $key => $item) {
                    $distirct = DB::table('district')->whereId($item)->first();
                    if ($distirct) {
                        $expert['data']->addr[$key] = $distirct->name;
                    }
                }
                $expert['data']->cate = explode(',', $expert['data']->cate);
                foreach ($expert['data']->cate as $key => $item) {
                    $distirct = DB::table('cate')->whereId($item)->first();
                    if ($distirct) {
                        $expert['data']->cate[$key] = $distirct->name;
                    }
                }
                $result['id'] = (string)$expert['data']->id;
                $result['place'] = (string)$expert['data']->position;
                $result['imageUrl'] = (string)$expert['data']->head_img;
                $result['preference'] = (string)$expert['data']->year . '年从业经验, ' . $expert['data']->addr[0] . ' ' . $expert['data']->addr[1];
                $result['recommendation'] = (string)$expert['data']->recommend;
                $result['cate'] = $expert['data']->cate;
                $result['pleased'] = (string)$expert['data']->satisfaction;
                $result['enquiry'] = (string)$expert['data']->ask_num;
                $result['nickName'] = (string)$expert['data']->name;
                $users = DB::table('users')->where('name', $expert['data']->name)->first();
                if ($users) {
                    $result['friendUid'] = (string)$users->id;
                } else {
                    $result['friendUid'] = (string)0;
                }
                return $this->formateResponse(1000, 'success', $result);
            } else {
                return $this->formateResponse(2001, '数据错误~');
            }
        } else {
            return $this->formateResponse(2001, '数据错误~');
        }
    }

    public function detail($id)
    {
        $expert['data'] = DB::table('experts')
            ->select('experts.*', 'position.position as position')
            ->leftJoin('position', 'experts.position', '=', 'position.id')
            ->where('experts.id', $id)
            ->first();
        $result = [];
//        id					string	  推荐专家ID
//        nickName			string	专家昵称
//        place				string	职位
//        imageUrl			string	专家头像的URL
//        preference			string	工作年限及城市
//        recommendationRate	string	推荐指数
//        pleased				string	满意度
//        enquiry				tring	咨询量
//        friendUid			string	对方的 uid   发送临时消息使用
        if ($expert['data']) {
            //获取服务履历
            $work = DB::table('experts_work')
                ->leftJoin('position', 'experts_work.position', '=', 'position.id')
                ->where('eid', $id)->first();
//            id				string	履历信息id
//            companyName		string	公司名称
//            businessType	string	  业务类型
//            workDescription	string	  工作描述
            if ($work) {
                $result['work']['id'] = (string)$work->id;
                $result['work']['companyName'] = (string)$work->company;
                $result['work']['businessType'] = (string)$work->position;
                $result['work']['workDescription'] = (string)$work->work;
            } else {
                $result['work'] = (string)0;
            }
            $expert['data']->addr = explode('-', $expert['data']->addr);
            foreach ($expert['data']->addr as $key => $item) {
                $distirct = DB::table('district')->whereId($item)->first();
                if ($distirct) {
                    $expert['data']->addr[$key] = $distirct->name;
                }
            }
            $expert['data']->cate = explode(',', $expert['data']->cate);
            foreach ($expert['data']->cate as $key => $item) {
                $distirct = DB::table('cate')->whereId($item)->first();
                if ($distirct) {
                    $expert['data']->cate[$key] = $distirct->name;
                }
            }
            $result['id'] = (string)$expert['data']->id;
            $result['place'] = (string)$expert['data']->position;
            $result['imageUrl'] = (string)$expert['data']->head_img;
            $result['preference'] = (string)$expert['data']->year . '年从业经验, ' . $expert['data']->addr[0] . ' ' . $expert['data']->addr[1];
            $result['recommendation'] = (string)$expert['data']->recommend;
            $result['cate'] = $expert['data']->cate;
            $result['pleased'] = (string)$expert['data']->satisfaction;
            $result['enquiry'] = (string)$expert['data']->ask_num;
            $result['nickName'] = (string)$expert['data']->name;
            $result['responseTime'] = (string)$expert['data']->do_time;
            $result['helpUser'] = (string)$expert['data']->service_num;
            $result['introduce'] = (string)$expert['data']->detail;
            $users = DB::table('users')->where('name', $expert['data']->name)->first();
            if ($users) {
                $result['friendUid'] = (string)$users->id;
            } else {
                $result['friendUid'] = (string)0;
            }
            return $this->formateResponse(1000, 'success', $result);
        } else {
            return $this->formateResponse(2001, '数据错误~');
        }
    }

    public function recommendation()
    {
        $expert['data'] = DB::table('experts')
            ->select('experts.*', 'position.position as position')
            ->leftJoin('position', 'experts.position', '=', 'position.id')
            ->orderBy('recommend', 'desc')
            ->limit(10)
            ->get();
        $result = [];
//        id					string	  推荐专家ID
//        nickName			string	专家昵称
//        place				string	职位
//        imageUrl			string	专家头像的URL
//        preference			string	工作年限及城市
//        recommendationRate	string	推荐指数
//        pleased				string	满意度
//        enquiry				tring	咨询量
//        friendUid			string	对方的 uid   发送临时消息使用
        if ($expert['data']) {
            $result['count']=intval(count($expert['data']));
            foreach ($expert['data'] as $k => $v) {
                $expert['data'][$k]->addr = explode('-', $expert['data'][$k]->addr);
                foreach ($expert['data'][$k]->addr as $key => $item) {
                    $distirct = DB::table('district')->whereId($item)->first();
                    if ($distirct) {
                        $expert['data'][$k]->addr[$key] = $distirct->name;
                    }
                }
                $expert['data'][$k]->cate = explode(',', $expert['data'][$k]->cate);
                foreach ($expert['data'][$k]->cate as $key => $item) {
                    $distirct = DB::table('cate')->whereId($item)->first();
                    if ($distirct) {
                        $expert['data'][$k]->cate[$key] = $distirct->name;
                    }
                }
                $result[$k]['id'] = (string)$expert['data'][$k]->id;
                $result[$k]['place'] = (string)$expert['data'][$k]->position;
                $result[$k]['imageUrl'] = (string)$expert['data'][$k]->head_img;
                $result[$k]['preference'] = (string)$expert['data'][$k]->year . '年从业经验, ' . $expert['data'][$k]->addr[0] . ' ' . $expert['data'][$k]->addr[1];
                $result[$k]['recommendation'] = (string)$expert['data'][$k]->recommend;
                $result[$k]['cate'] = $expert['data'][$k]->cate;
                $result[$k]['pleased'] = (string)$expert['data'][$k]->satisfaction;
                $result[$k]['enquiry'] = (string)$expert['data'][$k]->ask_num;
                $result[$k]['nickName'] = (string)$expert['data'][$k]->name;
                $users = DB::table('users')->where('name', $expert['data'][$k]->name)->first();
                if ($users) {
                    $result[$k]['friendUid'] = (string)$users->id;
                } else {
                    $result[$k]['friendUid'] = (string)0;
                }
            }
            return $this->formateResponse(1000, 'success', $result);
        } else {
            return $this->formateResponse(2001, '数据错误~');
        }
    }

    public function arbitration(Request $request)
    {
        if ($request->get('token')) {
            $tokenInfo = Crypt::decrypt($request->get('token'));
//            $tokenInfo = Crypt::decrypt(urldecode($request->get('token')));
            $this->uid = $tokenInfo['uid'];
            if ($this->uid) {
                $work = DB::table('work')->where('task_id', $request->get('task_id'))->first();
                $task = DB::table('task')->where('id', $request->get('task_id'))->first();
                if ($this->uid == $task->uid || $this->uid == $work->uid) {
                    $res = DB::table('experts_task')->where('task_id', $request->task_id)->update(['detail' => $request->get('decription')]);
                    if ($res) {
                        return $this->formateResponse(1000, 'status', (string)1);
                    } else {
                        return $this->formateResponse(2000, 'status', (string)0);
                    }
                } else {
                    return $this->formateResponse(2000, 'status', (string)0);
                }
            } else {
                return $this->formateResponse(2000, 'status', (string)0);
            }
        } else {
            return $this->formateResponse(2000, 'status', (string)0);
        }
    }
}