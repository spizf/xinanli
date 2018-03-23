<?php

namespace App\Modules\Test\Http\Controllers;
use App\Http\Controllers\IndexController as BasicIndexController;
use App\Http\Requests;
use App\Modules\Test\Model\Common;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;

class MarketingController extends  BasicIndexController{
    public function __construct()
    {
        parent::__construct();
        $this->user = Auth::user();
        $this->initTheme('market');
    }
    public function  testCallback(Request $request){




        $accessTokenArr = array(
            'client_id' => $request->get('client_id'),
            'grant_type' => 'authorization_code',
            'redirect_uri' => $request->get('redirect_uri'),
            'code' => $request->get('code'),
            'client_secret' => $request->get('client_secret'),
            'token' =>$request->get('token')
        );
        $accessTokenUrl = Common::KPPWACCESSTOKENURL;
      

        $accessTokenResult= json_decode(Common::sendPostRequest($accessTokenUrl,$accessTokenArr),true);
        dd($accessTokenResult);




    }
    public function evaluation(Request $request){
        $this->theme->setTitle('信用评价');
        return $this->theme->scope('test.evaluation')->render();
    }
    public function trusteeship(Request $request){
        $this->theme->setTitle('资金托管');
        return $this->theme->scope('test.trusteeship')->render();
    }
    public function arbitration(Request $request){
        $this->theme->setTitle('技术仲裁');
        return $this->theme->scope('test.arbitration')->render();
    }

}