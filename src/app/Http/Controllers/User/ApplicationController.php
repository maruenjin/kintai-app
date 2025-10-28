<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicationRequest;
use App\Models\{Attendance, Application};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ApplicationController extends Controller
{
    
    public function index()
    {
         $user = Auth::user();
        $pending  = Application::where('user_id',$user->id)->where('status',0)->latest()->get();
        $approved = Application::where('user_id',$user->id)->where('status',1)->latest()->get();
        return view('user.apps.index', compact('pending','approved'));
    }

    
    public function create(Attendance $attendance)
    {
        
        $this->authorize('view', $attendance);
        $attendance->load('breaks');

        return view('user.apps.create', compact('attendance'));
    }

    
    public function store(AttendanceApplicationRequest $request)
    {
        $this->authorize('view', $attendance);

       
        $breaks = collect($req->input('breaks', []))
            ->filter(fn($b)=>!empty($b['start']) || !empty($b['end']))
            ->map(function($b){
                return [
                    'start' => !empty($b['start']) ? Carbon::parse($b['start'])->toDateTimeString() : null,
                    'end'   => !empty($b['end'])   ? Carbon::parse($b['end'])->toDateTimeString()   : null,
                ];
            })->values()->all();

        DB::transaction(function() use ($req,$attendance,$breaks){
            Application::create([
                'attendance_id' => $attendance->id,
                'user_id'       => Auth::id(),
                'type'          => 1, 
                'work_date'     => $attendance->work_date,
                'clock_in'      => $req->filled('clock_in')  ? Carbon::parse($req->clock_in)  : null,
                'clock_out'     => $req->filled('clock_out') ? Carbon::parse($req->clock_out) : null,
                'breaks'        => $breaks ?: null, 
                'note'          => $req->input('note'),
                'status'        => 0, 
            ]);
        });

        return redirect()->route('user.apps.index')->with('status','修正申請を送信しました（承認待ち）');
    }
    
}

