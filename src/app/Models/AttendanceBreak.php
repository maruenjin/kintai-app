<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceBreak extends Model
{
    protected $fillable = ['attendance_id','break_start','break_end','duration_minutes'];
    protected $casts = [
        'break_start' => 'datetime',
        'break_end'   => 'datetime',
    ];

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function finalizeDuration(): void
    {
        if ($this->break_start && $this->break_end) {
            $this->duration_minutes = $this->break_end->diffInMinutes($this->break_start);
            $this->save();
        }
    }
}
