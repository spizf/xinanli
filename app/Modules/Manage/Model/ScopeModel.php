<?php

namespace App\Modules\Manage\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ScopeModel extends Model
{
    
    protected $table = 'scope';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id','name','cate_id','sort'
    ];

    public $timestamps = false;


}
