<?php

namespace App\Http\Controllers\User;


use App\Http\Requests\AttendanceApplicationRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreApplicationRequest;
use App\Models\{Attendance, Application};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ApplicationController extends Controller
{
    
    public function index(Request $request) 
    {
         $status = $request->query('status', 'pending');
         $uid    = Auth::id();

         $base = Application::where('user_id', $uid)->latest('id');

         if ($status === 'approved') {
        $apps = (clone $base)->where('status', 1)->paginate(20)->withQueryString();
        } else { 
        $status = 'pending';
        $apps = (clone $base)->where('status', 0)->paginate(20)->withQueryString();
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

    if (!empty($data['break1_start']) || !empty($data['break1_end'])) {
        $breaks[] = [
            'start' => $data['break1_start'] ?: null,
            'end'   => $data['break1_end']   ?: null,
        ];
    }

    if (!empty($data['break2_start']) || !empty($data['break2_end'])) {
        $breaks[] = [
            'start' => $data['break2_start'] ?: null,
            'end'   => $data['break2_end']   ?: null,
        ];
    }

    Application::create([
        'attendance_id' => $attendance->id,
        'user_id'       => $request->user()->id,
        'type'          => 1, 
        'work_date'     => $attendance->work_date,
        'clock_in'      => $data['clock_in'],     
        'clock_out'     => $data['clock_out'],     
        'breaks'        => $breaks,               
        'note'          => $data['reason'],        
        'status'        => 0, 
    ]);

    return redirect()
        ->route('user.apps.index')
        ->with('status', '修正申請を送信しました。');
}

}

