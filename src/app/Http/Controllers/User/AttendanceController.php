<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    
    public function index()
{
    $user   = auth()->user();
    $today  = Carbon::today();

    $attendance = Attendance::with('breaks')->firstOrNew([
        'user_id'   => $user->id,
        'work_date' => $today->toDateString(),
    ]);

    
    $latestBreak = $attendance->exists
        ? $attendance->breaks()->orderByDesc('break_start')->first()
        : null;

    $isOnBreak  = $latestBreak && is_null($latestBreak->break_end);
    $isWorking  = $attendance->clock_in && is_null($attendance->clock_out);
    $isFinished = $attendance->clock_out !== null;

    $status = $isOnBreak
        ? '休憩中'
        : ($isWorking ? '出勤中' : ($isFinished ? '退勤済み' : '勤務外'));

    return view('user.attendance.index', compact(
        'today', 'attendance', 'isOnBreak', 'isWorking', 'isFinished', 'status'
    ));
}


    
    public function clockIn(Request $request)
    {
        $a = $this->today($request);
        abort_if($a->clock_in, 400, '出勤は1日に1回だけです');
        $a->update(['clock_in' => now(), 'status' => 1]);
        return back()->with('status', '出勤しました。');
    }

    
    public function breakIn(Request $request)
    {
        $user = Auth::user();

    return DB::transaction(function () use ($user) {
        $today = now()->toDateString();

        $attendance = Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->lockForUpdate()
            ->first();

        if (!$attendance) {
            return back()->withErrors(['break' => '本日の勤怠が見つかりません']);
        }
        if ((int)$attendance->status !== Attendance::STATUS_WORKING) {
            return back()->withErrors(['break' => '現在のステータスでは休憩に入れません']);
        }

        
        $open = $attendance->breaks()->whereNull('break_end')->exists();
        if ($open) {
            return back()->withErrors(['break' => '終了していない休憩があります']);
        }

        $attendance->breaks()->create([
            'break_start' => now(),
            
        ]);

        $attendance->status = Attendance::STATUS_BREAK;
        $attendance->save();

        return back()->with('status', '休憩に入りました');
    });
    }

    
    public function breakOut(Request $request)
    {
        $user = Auth::user();

        return DB::transaction(function () use ($user) {

           
            $today = now()->toDateString();

            /** @var Attendance|null $attendance */
            $attendance = Attendance::query()
                ->where('user_id', $user->id)
                ->whereDate('work_date', $today)
                ->lockForUpdate() 
                ->first();

            if (!$attendance) {
                return back()->withErrors(['break' => '本日の勤怠が見つかりません']);
            }

            if (!in_array((int)$attendance->status, [Attendance::STATUS_BREAK, Attendance::STATUS_WORKING], true)) {
                return back()->withErrors(['break' => '現在のステータスでは休憩を終了できません']);
            }

            
            /** @var AttendanceBreak|null $br */
            $br = $attendance->breaks()
                ->whereNull('break_end')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (!$br) {
                return back()->withErrors(['break' => '開始中の休憩がありません']);
            }

            
            $br->break_end = now();
           
            $br->duration_minutes = max(0, $br->break_end->diffInMinutes($br->break_start));
            $br->save();

           
            $attendance->status = Attendance::STATUS_WORKING;
            $attendance->save();

            return back()->with('status', '休憩を終了しました');
        });
    }

    

    
    public function clockOut(Request $request)
    {
        $a = $this->today($request);
        abort_if(!$a->clock_in, 400, '先に出勤してください');
        abort_if($a->clock_out, 400, '退勤は1日に1回だけです');

        
        if ($last = $a->breaks()->whereNull('break_end')->latest('break_start')->first()) {
            $last->update(['break_end' => now()]);
        }

        $a->update(['clock_out' => now(), 'status' => 3]);
        return back()->with('status', 'お疲れ様でした。');
    }

    
    private function today(Request $request): Attendance
    {
        return Attendance::firstOrCreate([
            'user_id'   => $request->user()->id,
            'work_date' => Carbon::today()->toDateString(),
        ]);
    }

    
    private function statusLabel(Attendance $a): string
    {
        if ($a->clock_out) return '退勤済';
        if ($a->breaks()->whereNull('break_end')->exists()) return '休憩中';
        if ($a->clock_in) return '出勤中';
        return '勤務外';
    }
}

