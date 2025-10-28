<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\UpdateAttendanceRequest;
use App\Models\AttendanceBreak;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date', Carbon::today()->toDateString()); 
        
        $items = \App\Models\Attendance::with(['user','breaks'])
            ->whereDate('work_date', \Carbon\Carbon::parse($date)->toDateString())
            ->get()
            ->sortBy(fn($a) => $a->user->name ?? '');

        return view('admin.attendance.index', compact('items', 'date'));
    }

    public function show(\App\Models\Attendance $attendance)
    {
        $attendance->load(['user', 'breaks' => fn($q) => $q->orderBy('break_start')]);
        return view('admin.attendance.show', compact('attendance'));
    }

    public function update(UpdateAttendanceRequest $request, \App\Models\Attendance $attendance)
    {
    $date = $attendance->work_date->toDateString();

    $in  = $request->filled('clock_in')  ? Carbon::parse("$date ".$request->clock_in)  : null;
    $out = $request->filled('clock_out') ? Carbon::parse("$date ".$request->clock_out) : null;

    $attendance->update([
        'clock_in'  => $in,
        'clock_out' => $out,
        'note'      => $request->note,
        'status'    => ($in && $out) ? 3 : ($in ? 1 : 0), 
    ]);

    
    $keepIds = [];
    foreach ([1,2] as $i) {
        $sKey = "b{$i}_start";
        $eKey = "b{$i}_end";
        if ($request->filled($sKey) && $request->filled($eKey)) {
            $bs = Carbon::parse("$date ".$request->input($sKey));
            $be = Carbon::parse("$date ".$request->input($eKey));

            $break = AttendanceBreak::updateOrCreate(
                ['attendance_id' => $attendance->id, 'break_start' => $bs],
                ['break_end' => $be]
            );
            $keepIds[] = $break->id;
        }
    }
    
    $attendance->breaks()->whereNotIn('id', $keepIds)->delete();

    return redirect()
        ->route('admin.attendance.show', $attendance)
        ->with('status', '修正しました。');
}
}

