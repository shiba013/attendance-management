<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class workRequestTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_request_id',
        'rest_id',
        'status',
        'before_time',
        'after_time',
    ];

    protected $casts = [
        'before_time' => 'datetime',
        'after_time' => 'datetime',
    ];

    public function rest()
    {
        return $this->belongsTo(Rest::class);
    }

    public function workRequest()
    {
        return $this->belongsTo(WorkRequest::class);
    }
}
