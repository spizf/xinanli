<?php

namespace App\Modules\Task\Model;

use Illuminate\Database\Eloquent\Model;

class TaskReasonModel extends Model
{
    protected $table = "task_reason";

    public $timestamps = true;
    protected $fillable = [
        'user_id','employer_id','task_id','reason','nums','accessory','created_at','updated_at'
    ];
}
