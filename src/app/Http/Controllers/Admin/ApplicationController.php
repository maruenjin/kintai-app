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
        $status = $request->input('status', 'pending'); // pending|approved|rejected
        $q = AttendanceApplication::with(['attendance','user']);
        if ($status === 'pending')  $q->where('status',0);
        if ($status === 'approved') $q->where('status',1);
        if ($status === 'rejected') $q->where('status',2);

        $apps = $q->orderBy('id','desc')->paginate(30);
        return view('admin.apps.index', compact('apps','status'));
    }

    public function show(AttendanceApplication $app)
    {
        $app->load(['attendance.user']);
        return view('admin.apps.show', compact('app'));
    }

    public function approve(Request $request, AttendanceApplication $app)
    {
        if ($app->status !== 0) return back()->withErrors(['status'=>'すでに処理済みです。']);

        DB::transaction(function () use ($app, $request) {
            $attendance = Attendance::lockForUpdate()->find($app->attendance_id);

            if ($app->work_date) $attendance->work_date = $app->work_date;
            if ($app->clock_in)  $attendance->clock_in  = Carbon::parse($app->clock_in);
            if ($app->clock_out) $attendance->clock_out = Carbon::parse($app->clock_out);
            if ($app->note)      $attendance->note      = $app->note;

            // ステータスを退勤済に寄せる（clock_outがあれば3）
            if ($attendance->clock_out) $attendance->status = 3;

            $attendance->save();

            $app->status = 1;
            $app->approved_by = Auth::id();
            $app->approved_at = now();
            $app->manager_comment = $request->input('manager_comment');
            $app->save();
        });

        return redirect()->route('admin.apps.index')->with('status','承認しました。');
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

