<?php
namespace App\Modules\Article\Http\Controllers;

use App\Http\Controllers\IndexController;
use App\Http\Requests;
use App\Modules\Article\Model\ArticleModel;
use App\Modules\Manage\Model\ArticleCategoryModel;
use Illuminate\Http\Request;
use App\Modules\Advertisement\Model\AdTargetModel;
use App\Modules\Advertisement\Model\RePositionModel;
use App\Modules\Advertisement\Model\RecommendModel;
use App\Modules\Manage\Model\ConfigModel;
use Cache;

class ToolController extends IndexController
{
    public function __construct()
    {
        parent::__construct();

        $this->initTheme('tool');
    }

    public function tool(Request $request)
    {
        $this->theme->setTitle('工具|法定评价管理云平台');
        $this->theme->set('now_menu', '/article/tool');
        return $this->theme->scope('bre.tool')->render();
    }

    public function biaozun(Request $request)
    {
        $this->theme->setTitle('工具|法定评价管理云平台');
        $this->theme->set('now_menu', '/article/tool');
        return $this->theme->scope('bre.biaozun')->render();
    }
}

















