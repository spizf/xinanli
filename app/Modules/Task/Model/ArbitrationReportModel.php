<?php
namespace App\Modules\Task\Model;

use Illuminate\Database\Eloquent\Model;

class ArbitrationReportModel extends Model
{
    protected $table = "arbitration_report";

    public $timestamps = true;
    protected $fillable = [
        'expert_id','task_id','expert_array','attachment','created_at','updated_at'
    ];
}