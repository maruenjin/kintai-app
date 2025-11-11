<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Attendance extends Model
{
    public const STATUS_OFF     = 0; 
    public const STATUS_WORKING = 1; 
    public const STATUS_BREAK   = 2; 
    public const STATUS_DONE    = 3; 

    protected $fillable = [
        'user_id', 'work_date', 'clock_in', 'clock_out',
        'status', 'note',       
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

    
    private function toCarbonOnWorkDate($value): ?Carbon
    {
        if (!$value) return null;
        if ($value instanceof Carbon) return $value;

        $date = $this->work_date instanceof Carbon
            ? $this->work_date->format('Y-m-d')
            : (string) $this->work_date;

        
        $str = (preg_match('/^\d{2}:\d{2}(:\d{2})?$/', (string)$value))
            ? ($date . ' ' . $value)
            : (string)$value;

        return Carbon::parse($str);
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
        $in  = $this->toCarbonOnWorkDate($this->clock_in);
        $out = $this->toCarbonOnWorkDate($this->clock_out);
        if (!$in || !$out) return 0;

        $gross  = max(0, $out->diffInMinutes($in)); 
        $breaks = $this->breakMinutes();           
        return max(0, $gross - $breaks);            
    }

    
    public function getWorkedMinutesAttribute(): ?int
    {
        $in  = $this->toCarbonOnWorkDate($this->clock_in);
        $out = $this->toCarbonOnWorkDate($this->clock_out);
        if (!$in || !$out) return null;
        $gross  = max(0, $out->diffInMinutes($in));
        $breaks = $this->breakMinutes();
        return max(0, $gross - $breaks);
    }

    public function getBreakMinutesAttribute(): int
    {
        return $this->breakMinutes();
    }

    public function getTotalMinutesAttribute(): ?int
    {
       
        return $this->worked_minutes; 
    }

   
    public function getWorkedHmAttribute(): string
    {
        $m = $this->workedMinutes();
        $h = intdiv($m, 60);
        $r = $m % 60;
        return sprintf('%02d:%02d', $h, $r);
    }

    public function getBreakHmAttribute(): string
    {
        return self::minToLabel($this->breakMinutes());
    }

    public function getTotalHmAttribute(): string
    {
        
        $m = $this->total_minutes ?? 0;
        return self::minToLabel($m);
    }

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
       
        return;
    }

    
    public function applications(): HasMany
    {
        return $this->hasMany(\App\Models\AttendanceApplication::class);
    }
}
