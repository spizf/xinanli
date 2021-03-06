<?php

namespace App\Modules\Manage\Model;
use Zizaco\Entrust\EntrustPermission;

class Permission extends EntrustPermission
{
    
    protected $table = 'permissions';

    protected $fillable = [
        'name', 'display_name', 'description','module_types','created_at', 'updated_at', 'pid','sort','level','route'
    ];
    public  $timestamps = false;  

    static public function getPermissionMenu()
    {
        
        $menu_all = MenuModel::all()->toArray();
        foreach($menu_all as $k=>$v)
        {
            $menu_all[$k]['fid'] = $v['id'];
        }
        
        $permission_all = self::all()->toArray();
        
        $menu_permission = MenuPermissionModel::all()->toArray();
        $menu_permission = \CommonClass::keyBy($menu_permission,'permission_id');
        
        foreach($permission_all as $k=>$v)
        {
            if($v['id'] != 99 && $v['id'] != 380 && $v['id'] != 381 && $v['id'] != 383 && $v['id'] != 384 && $v['id'] != 385 && $v['id'] != 386 && $v['id'] != 387 && $v['id'] != 389 && $v['id'] != 390 && $v['id'] != 391 && $v['id'] != 392){
            $permission_all[$k]['pid'] = $menu_permission[$v['id']]['menu_id'];
            $permission_all[$k]['fid'] = 0;
            $permission_all[$k]['name'] = $v['display_name'];
        }
        }
        
        $permission_menu = array_merge($menu_all,$permission_all);
        $permission_menu_tree = \CommonClass::listToTree($permission_menu,'fid','pid');
        return $permission_menu_tree;
    }
}
