<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    protected $table = 'attendance_applications';

    protected $fillable = [
        'attendance_id','user_id','type','work_date',
        'clock_in','clock_out','breaks','note',
        'status','approved_by','approved_at','manager_comment'
    ];

    protected $casts = [
        'work_date'  => 'date',
        'clock_in'   => 'datetime',
        'clock_out'  => 'datetime',
        'approved_at'=> 'datetime',
        'breaks'     => 'array', 
    ];

    public function attendance(): BelongsTo { return $this->belongsTo(\App\Models\Attendance::class); }
    public function user(): BelongsTo { return $this->belongsTo(\App\Models\User::class); }
}
