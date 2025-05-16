<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_id',
        'user_id',
        'requested_start',
        'requested_end',
        'remarks',
        'requested_rests_start',
        'requested_rests_end',
        'reviewed_by_user_id',
        'reviewed_at',
        'status',
    ];

    protected $casts = [
        'requested_start' => 'datetime',
        'requested_end' => 'datetime',
        'requested_rests_start' => 'datetime',
        'requested_rests_end' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function work()
    {
        return $this->belongsTo(Work::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(WorkRequest::class, 'reviewed_by_user_id');
    }
}
