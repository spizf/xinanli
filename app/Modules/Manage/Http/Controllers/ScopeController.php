<?php
namespace App\Modules\Manage\Http\Controllers;

use App\Http\Controllers\ManageController;
use App\Http\Requests;
use App\Http\Controllers\BasicController;
use App\Modules\Manage\Model\ScopeModel;
use Illuminate\Http\Request;
use App\Modules\Manage\Http\Requests\ScopeRequest;
use App\Modules\Task\Model\TaskCateModel;
use Illuminate\Support\Facades\Auth;


class ScopeController extends ManageController
{
	public function __construct()
    {
        parent::__construct();
        $this->initTheme('manage');
        $this->theme->set('manageType', 'scope');
        $this->theme->setTitle('业务范围');

    }

    
    public function scopelist(Request $request)
    {
        
        $navRes = ScopeModel::whereRaw('1 = 1');
        $by = $request->get('by') ? $request->get('by') : 'sort';
        $order = $request->get('sort') ? $request->get('sort') : 'desc';
        $paginate = $request->get('paginate') ? $request->get('paginate') : 10;
        $scopeRes = $navRes->orderBy($by, $order)->paginate($paginate);
        foreach ($scopeRes As $k=>$v){
            $tc = TaskCateModel::findById($v['cate_id']);
            $scopeRes[$k]['catename'] = $tc['name'];
        }
        $data = array(
            'scope_list' => $scopeRes,
            'paginate' => $paginate
        );
        return $this->theme->scope('manage.scopelist',$data)->render();
    }

    
    public function addScope()
    {
        $cateFirst = TaskCateModel::findByPid([0],['id','name']);
        $view = [
            'cate_first' => $cateFirst
        ];
        return $this->theme->scope('manage.addscope',$view)->render();
    }

    
    public function postAddScope(ScopeRequest $request)
    {
        $data = $request->all();
        $res = ScopeModel::create($data);
        if($res)
        {
            return redirect('manage/scope')->with(array('message' => '操作成功'));
        }
    }

    
    public function editScope($id)
    {
        $id = intval($id);
        
        $scopeInfo = ScopeModel::where('id',$id)->get()->toArray();
        $cateFirst = TaskCateModel::findByPid([0],['id','name']);

        $data = array(
            'scopeInfo' => $scopeInfo,
            'cate_first' => $cateFirst
        );
        return $this->theme->scope('manage.editscope',$data)->render();
    }

    
    public function postEditScope(ScopeRequest $request)
    {
        $data = $request->all();
        $arr = array(
            'name' => $data['name'],
            'sort' => $data['sort'],
            'cate_id' => $data['cate_id'],
        );
        
        $res = ScopeModel::where('id',$data['id'])->update($arr);
        if($res)
        {
            return redirect('manage/scope')->with(array('message' => '操作成功'));
        }
    }

    
    public function deleteScope($id)
    {
        $id = intval($id);
        $res = ScopeModel::where('id',$id)->delete();
        if(!$res)
        {
            return redirect()->to('/manage/scope')->with(array('message' => '操作失败'));
        }
        return redirect()->to('/manage/scope')->with(array('message' => '操作成功'));
    }













}
