<?php
namespace App\Modules\Task\Model;

use Illuminate\Database\Eloquent\Model;

class TaskReasonattachmentModel extends Model
{
    protected $table = "task_reasonattachment";

    public $timestamps = true;
    protected $fillable = [
        'user_id','task_id','type','attachment_id','created_at','updated_at'
    ];
}