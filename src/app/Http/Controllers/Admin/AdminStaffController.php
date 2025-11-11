<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminStaffController extends Controller
{
    
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q', ''));

        $users = User::query()
            ->where('role', 0)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        
        $currentYm = Carbon::now()->format('Y-m');

        return view('admin.staffs.index', compact('users', 'q', 'currentYm'));
    }

   
    public function monthly(User $user, Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));

    
    [$y, $m] = explode('-', $month);
    $start = \Carbon\Carbon::createFromDate((int)$y, (int)$m, 1)->startOfMonth();
    $end   = (clone $start)->endOfMonth();

    $attendances = \App\Models\Attendance::query()
      ->withSum('breaks as breaks_sum_duration_minutes', 'duration_minutes')
      ->where('user_id', $user->id)
      ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
      ->orderBy('work_date')
      ->get();

    return view('admin.staffs.monthly', [
       'user' => $user,
       'month' => $month,
       'attendances' => $attendances,
       'prevMonth' => $start->copy()->subMonth()->format('Y-m'),
       'nextMonth' => $start->copy()->addMonth()->format('Y-m'),
       'labelYm'    => $start->format('Y/m'),
    

    ]);
    }

     public function csv(User $user, Request $request): StreamedResponse
    {
        $month = $request->get('month', now()->format('Y-m'));
        [$y, $m] = explode('-', $month);
        $start = Carbon::createFromDate((int)$y, (int)$m, 1)->startOfMonth();
        $end   = (clone $start)->endOfMonth();

        $rows = \App\Models\Attendance::query()
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->withSum('breaks as breaks_sum_duration_minutes', 'duration_minutes')
            ->orderBy('work_date')
            ->get()
            ->map(function ($a) {
                $fmt = fn ($minutes) => $minutes === null ? '' : sprintf('%02d:%02d', intdiv((int)$minutes, 60), (int)$minutes % 60);

                $clockIn  = $a->clock_in ? $a->clock_in->format('H:i') : '';
                $clockOut = $a->clock_out ? $a->clock_out->format('H:i') : '';
                $breakMin = (int)($a->breaks_sum_duration_minutes ?? 0);

                $worked = null;
                if ($a->clock_in && $a->clock_out) {
                    $worked = max(0, $a->clock_out->diffInMinutes($a->clock_in) - $breakMin);
                }

                $dow = ['日','月','火','水','木','金','土'][$a->work_date->dayOfWeek];

                return [
                    $a->work_date->format("m/d（{$dow}）"),
                    $clockIn,
                    $clockOut,
                    $fmt($breakMin),
                    $fmt($worked),
                    $a->note ?? '',
                ];
            });

        
        $safeName = preg_replace('/[\\\\\\/:"*?<>|]+/', '_', $user->name);
        $filename = sprintf('%s_%s.csv', $safeName, $start->format('Ym'));

        return new StreamedResponse(function () use ($rows) {
            $out = fopen('php://output', 'w');
           
            fwrite($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
           
            fputcsv($out, ['日付', '出勤', '退勤', '休憩', '合計', '備考']);
           
            foreach ($rows as $r) {
                fputcsv($out, $r);
            }
            fclose($out);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
