<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceApplicationRequest;
use App\Models\Attendance;
use App\Models\AttendanceApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApplicationController extends Controller
{
    
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        $userId = Auth::id();

        
        $base = AttendanceApplication::where('user_id', $userId)
            ->latest('id');

        if ($status === 'approved') {
            $apps = (clone $base)
                ->where('status', 1)   
                ->paginate(20)
                ->withQueryString();
        } else {
            $status = 'pending';
            $apps = (clone $base)
                ->where('status', 0)   
                ->paginate(20)
                ->withQueryString();
        }

        return view('user.applications.index', compact('apps', 'status'));
    }

    
    public function create(Attendance $attendance)
    {
        
        $this->authorize('view', $attendance);

        
        $attendance->load('breaks');

        return view('user.apps.create', compact('attendance'));
    }

   
    public function store(AttendanceApplicationRequest $request, Attendance $attendance)
    {
       
        $this->authorize('update', $attendance);

        
        $data = $request->validated();

        
        $breaks = [];

        foreach ([1, 2] as $i) {
            $start = $data["break{$i}_start"] ?? null;
            $end   = $data["break{$i}_end"]   ?? null;

            if ($start || $end) {
                $breaks[] = [
                    'start' => $start,
                    'end'   => $end,
                ];
            }
        }

       
        AttendanceApplication::create([
            'attendance_id' => $attendance->id,
            'user_id'       => $request->user()->id,
            'type'          => 1, 
            'work_date'     => $attendance->work_date,
            'clock_in'      => $data['clock_in']  ?? null,
            'clock_out'     => $data['clock_out'] ?? null,
            'breaks'        => $breaks,
            'note'          => $data['reason'],
            'status'        => 0, 
        ]);

        return redirect()
            ->route('user.apps.index')
            ->with('status', '修正申請を送信しました。');
    }
}

