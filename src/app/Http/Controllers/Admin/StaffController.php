<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StaffController extends Controller
{
    
    public function index(Request $request)
    {
        $q = trim((string)$request->input('q', ''));
        $staffs = User::query()
            ->where('role', '!=', 1)
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where('name', 'like', "%{$q}%")
                   ->orWhere('email', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return view('admin.staff.index', compact('staffs', 'q'));
    }

    
    public function monthly(Request $request, User $user)
    {
        $ym = $request->input('ym', now()->format('Y-m'));
        $first = Carbon::parse($ym . '-01')->startOfDay();
        $last  = (clone $first)->endOfMonth();

        $recs = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$first->toDateString(), $last->toDateString()])
            ->orderBy('work_date')
            ->get();

        
        $totalWork  = $recs->sum(function ($r) { return (int)$r->workMinutes(); });
        $totalBreak = $recs->sum(function ($r) { return (int)$r->breakMinutes(); });
        $totalNet   = max(0, $totalWork - $totalBreak);

        return view('admin.staff.monthly', [
            'user'       => $user,
            'ym'         => $ym,
            'prev'       => $first->copy()->subMonth()->format('Y-m'),
            'next'       => $first->copy()->addMonth()->format('Y-m'),
            'recs'       => $recs,
            'totalWork'  => $totalWork,
            'totalBreak' => $totalBreak,
            'totalNet'   => $totalNet,
        ]);
    }

   
    public function csv(Request $request, User $user): StreamedResponse
    {
        $ym = $request->input('ym', now()->format('Y-m'));
        $first = Carbon::parse($ym . '-01')->startOfDay();
        $last  = (clone $first)->endOfMonth();

        $rows = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$first->toDateString(), $last->toDateString()])
            ->orderBy('work_date')
            ->get();

        $filename = "attendance_{$user->id}_{$ym}.csv";

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            
            fputcsv($out, ['日付', '状態', '出勤', '退勤', '休憩合計(分)', '勤務合計(分)', '実働(分)', '備考']);
            foreach ($rows as $r) {
                $clockIn  = $r->clock_in ? $r->clock_in->format('Y-m-d H:i:s') : '';
                $clockOut = $r->clock_out ? $r->clock_out->format('Y-m-d H:i:s') : '';
                $breakMin = (int)$r->breakMinutes();
                $workMin  = (int)$r->workMinutes();
                $netMin   = max(0, $workMin - $breakMin);
                fputcsv($out, [
                    $r->work_date,
                    $r->statusLabel(),
                    $clockIn,
                    $clockOut,
                    $breakMin,
                    $workMin,
                    $netMin,
                    (string)$r->note,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
