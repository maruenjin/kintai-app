<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    
    public const STATUS_OFF     = 0; 
    public const STATUS_WORKING = 1; 
    public const STATUS_BREAK   = 2; 
    public const STATUS_DONE    = 3; 

    protected $fillable = [
        'user_id', 'work_date', 'clock_in', 'clock_out',
        'status', 'note', 'total_minutes', 
    ];

    protected $casts = [
        'work_date' => 'date',
        'clock_in'  => 'datetime',
        'clock_out' => 'datetime',
        'status'    => 'integer',
    ];

    

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function breaks(): HasMany
    {
        return $this->hasMany(AttendanceBreak::class);
    }

    
    public function breakMinutes(): int
    {
         if (isset($this->breaks_sum_duration_minutes)) {
        return (int) $this->breaks_sum_duration_minutes;
    }
   
    return (int) $this->breaks()->sum('duration_minutes');
    }

    
    public function workedMinutes(): int
    {
        if (!$this->clock_in || !$this->clock_out) return 0;
        $gross = $this->clock_out->diffInMinutes($this->clock_in);
        return max(0, $gross - $this->breakMinutes());
    }

    
    public function getBreakHmAttribute(): string { return self::minToLabel($this->breakMinutes()); }
    
    public function getTotalHmAttribute(): string { return self::minToLabel($this->workedMinutes()); }


    
    public static function minToLabel(int $m): string
    {
        return sprintf('%02d:%02d', intdiv($m, 60), $m % 60);
    }

    

    public function statusLabel(): string
    {
        return [
            self::STATUS_OFF     => '勤務外',
            self::STATUS_WORKING => '出勤中',
            self::STATUS_BREAK   => '休憩中',
            self::STATUS_DONE    => '退勤済',
        ][$this->status] ?? '勤務外';
    }

   

    public function scopeOfUser($q, $uid)
    {
        return $q->where('user_id', $uid);
    }

    
    public function finalizeTotals(): void
    {
        if ($this->clock_in && $this->clock_out) {
            $gross = $this->clock_out->diffInMinutes($this->clock_in);
            $break = (int) $this->breaks()->sum('duration_minutes'); 
            $this->total_minutes = max(0, $gross - $break);
            $this->save();
        }
    }
}

