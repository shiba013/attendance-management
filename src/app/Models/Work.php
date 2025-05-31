<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Work extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'start_time',
        'end_time',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rests()
    {
        return $this->hasMany(Rest::class);
    }

    public function workRequests()
    {
        return $this->hasMany(WorkRequest::class);
    }

    public function totalRestTime(): int
    {
        return $this->rests
        ->whereNotNull('start_time')
        ->whereNotNull('end_time')
        ->sum(function ($rest) {
            $start = Carbon::parse($rest->start_time)->copy()->second(0);
            $end = Carbon::parse($rest->end_time)->copy()->second(0);
            return $end->diffInMinutes($start);
        });
    }

    public function totalWorkTime(): int
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }
        $start = Carbon::parse($this->start_time)->copy()->second(0);
        $end = Carbon::parse($this->end_time)->copy()->second(0);
        $workMinutes = $end->diffInMinutes($start);
        return $workMinutes - $this->totalRestTime();
    }

    public function totalRestTimeFormat(): string
    {
        return gmdate('H:i', $this->totalRestTime()  * 60);
    }

    public function totalWorkTimeFormat(): string
    {
        return gmdate('H:i', $this->totalWorkTime()  * 60);
    }
}
