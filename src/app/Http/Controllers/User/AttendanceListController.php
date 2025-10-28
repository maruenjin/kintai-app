<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceListController extends Controller
{
   
    public function index(Request $request)
    {
        $user = Auth::user();

        
        $ymIn = (string) $request->query('ym', '');
        $ym   = preg_match('/^\d{4}-\d{2}$/', $ymIn) ? $ymIn : now()->format('Y-m');


        
        $first = Carbon::createFromFormat('Y-m-d', $ym . '-01')->startOfDay();
        $last  = (clone $first)->endOfMonth();

       
        $rows = Attendance::query()
            ->select(['id','user_id','work_date','clock_in','clock_out','status'])
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$first->toDateString(), $last->toDateString()])
            ->withSum('breaks as breaks_sum_duration_minutes', 'duration_minutes') 
            ->orderBy('work_date')
            ->get();

       
        $map = $rows->keyBy(fn ($a) => $a->work_date->toDateString());

        
        $days = [];
        for ($d = $first->copy(); $d->lte($last); $d->addDay()) {
            $date = $d->toDateString();
            $days[] = [
                'date'       => $date,
                'attendance' => $map->get($date),
            ];
        }

        return view('user.attendance.list', [
            'ym'   => $ym,
            'prev' => $first->copy()->subMonth()->format('Y-m'),
            'next' => $first->copy()->addMonth()->format('Y-m'),
            'days' => $days,
            'user' => $user,
        ]);
    }

   
    public function show(Attendance $attendance)
    {
        $this->authorize('view', $attendance);
       
        $attendance->load('breaks');

        return view('user.attendance.show', compact('attendance'));
    }
}


