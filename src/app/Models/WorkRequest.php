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
        'remarks',
        'reviewed_by_user_id',
        'reviewed_at',
        'status',
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

    public function times()
    {
        return $this->hasMany(WorkRequestTime::class);
    }
}
