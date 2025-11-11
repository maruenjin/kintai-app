<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceApplication extends Model
{
    protected $fillable = [
        'attendance_id','user_id','type',
        'work_date','clock_in','clock_out','note',
        'status','approved_by','approved_at','manager_comment',
    ];

    public function attendance(){ return $this->belongsTo(Attendance::class); }
    public function user(){ return $this->belongsTo(User::class); }

    public function isPending(): bool { return (int)$this->status === 0; }
}
