<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending'); 
       $q = AttendanceApplication::with(['user','attendance'])->latest('id');

       if ($status === 'approved')  $q->where('status', 1);
       elseif ($status === 'rejected') $q->where('status', 2);
       else { $status = 'pending';  $q->where('status', 0); }

       $apps = $q->paginate(30)->withQueryString();

       return view('admin.apps.index', compact('apps','status'));
    }

    public function show(AttendanceApplication $app)
    {
        $app->load(['attendance.user']);
        return view('admin.apps.show', compact('app'));
    }

    public function approve(Request $request, AttendanceApplication $app)
    {
       
        if ($app->status !== 0) {
            return back()->withErrors(['status' => 'すでに処理済みです。']);
        }

        DB::transaction(function () use ($app, $request) {

            
            $attendance = Attendance::withSum('breaks as breaks_sum_duration_minutes', 'duration_minutes')
                ->lockForUpdate()
                ->findOrFail($app->attendance_id);

           
            if (!empty($app->work_date)) {
                $attendance->work_date = $app->work_date;
            }
            if (!empty($app->clock_in)) {
                $attendance->clock_in = Carbon::parse($app->clock_in);
            }
            if (!empty($app->clock_out)) {
                $attendance->clock_out = Carbon::parse($app->clock_out);
            }
            if (!empty($app->note)) {
                $attendance->note = $app->note;
            }

            
            if (!empty($attendance->clock_out)) {
                $attendance->status = Attendance::STATUS_DONE;
            }

            
            $attendance->save();

            
            $app->status          = 1;            
            $app->approved_by     = Auth::id();
            $app->approved_at     = now();
            $app->manager_comment = $request->input('manager_comment');
            $app->save();
        });

        return redirect()
            ->route('admin.apps.show', $app);
            
    }


    public function reject(Request $request, AttendanceApplication $app)
    {
        if ($app->status !== 0) return back()->withErrors(['status'=>'すでに処理済みです。']);
        $app->status = 2;
        $app->approved_by = Auth::id();
        $app->approved_at = now();
        $app->manager_comment = $request->input('manager_comment');
        $app->save();

        return redirect()->route('admin.apps.index')->with('status','却下しました。');
    }
}

