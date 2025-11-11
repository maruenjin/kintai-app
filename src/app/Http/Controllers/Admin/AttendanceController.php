<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\UpdateAttendanceRequest;
use App\Models\AttendanceBreak;
use App\Http\Controllers\Controller;
use App\Models\User; 
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
{
   $targetDate = Carbon::parse($request->get('date', now()->toDateString()))->startOfDay();
        $prevDate   = $targetDate->copy()->subDay();
        $nextDate   = $targetDate->copy()->addDay();

        
        $rows = Attendance::query()
            ->with('user')
            ->withSum('breaks as breaks_sum_duration_minutes', 'duration_minutes')
            ->whereDate('work_date', $targetDate->toDateString())
            ->orderBy(
                User::select('name')->whereColumn('users.id', 'attendances.user_id')
            )
            ->get()
            
            ->map(function ($a) {
                $breakMin = (int)($a->breaks_sum_duration_minutes ?? 0);
                if ($a->clock_in && $a->clock_out) {
                    $worked = Carbon::parse($a->clock_in)->diffInMinutes(Carbon::parse($a->clock_out)) - $breakMin;
                    $worked = max($worked, 0);
                    $a->worked_hhmm = sprintf('%02d:%02d', intdiv($worked, 60), $worked % 60);
                } else {
                    $a->worked_hhmm = '';
                }
                return $a;
            });

        return view('admin.attendance.index', compact('rows', 'targetDate', 'prevDate', 'nextDate'));
}


    public function show(\App\Models\Attendance $attendance)
    {
         $attendance->load(['user','breaks']);
    return view('admin.attendance.show', compact('attendance'));
    }

    public function update(UpdateAttendanceRequest $request, \App\Models\Attendance $attendance)
{
    $data = $request->validated();

    $date = Carbon::parse($attendance->work_date)->toDateString();

    
    $in  = $data['clock_in_time']  ?? null;
    $out = $data['clock_out_time'] ?? null;

    $attendance->clock_in  = $in  ? Carbon::parse("$date $in:00")  : null;
    $attendance->clock_out = $out ? Carbon::parse("$date $out:00") : null;
    $attendance->note      = $data['note'] ?? null;
    $attendance->save();

    
    $rows = $attendance->breaks()->orderBy('break_start')->get()->values();

    for ($i = 0; $i < 2; $i++) {
        $start = data_get($data, "breaks.$i.start");
        $end   = data_get($data, "breaks.$i.end");

       
        if (!$start && !$end) {
            if ($rows->get($i)) $rows->get($i)->delete();
            continue;
        }

        /** @var AttendanceBreak $rec */
        $rec = $rows->get($i) ?: new AttendanceBreak(['attendance_id' => $attendance->id]);

        $rec->break_start = $start ? Carbon::parse("$date $start:00") : null;
        $rec->break_end   = $end   ? Carbon::parse("$date $end:00")   : null;
        $rec->duration_minutes = ($rec->break_start && $rec->break_end)
            ? $rec->break_end->diffInMinutes($rec->break_start)
            : 0;

        $rec->save();
    }

    
    if ($rows->count() > 2) {
        $attendance->breaks()
            ->orderBy('break_start')
            ->skip(2)
            ->take(PHP_INT_MAX)
            ->delete();
    }

    return redirect()
        ->route('admin.attendance.show', $attendance);
}

}