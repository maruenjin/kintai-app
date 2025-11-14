<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'user_id',
        'type',
        'work_date',
        'clock_in',
        'clock_out',
        'breaks',          
        'note',
        'status',
        'approved_by',
        'approved_at',
        'manager_comment',
    ];

    protected $casts = [
        'work_date' => 'date',
        'clock_in'  => 'datetime',
        'clock_out' => 'datetime',
        'breaks'    => 'array',   
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isPending(): bool
    {
        return (int) $this->status === 0;
    }
}

