@extends('layouts.admin')
@section('title','月次勤怠')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-staffs-monthly.css') }}?v={{ filemtime(public_path('css/admin-staffs-monthly.css')) }}">
@endpush

@section('content')
<div class="monthly-page">
  <h1 class="monthly-title u-title-bar">{{ $user->name }}さんの勤怠</h1>

  
  <div class="month-switch">
  <a class="ms-btn ms-prev"
     href="{{ route('admin.staffs.monthly', ['user'=>$user->id, 'month'=>$prevMonth]) }}">← 前月</a>

   <form method="GET" action="{{ route('admin.staffs.monthly', ['user'=>$user->id]) }}" class="ms-date">
  <div class="ms-date-inner">
    <svg class="ms-cal-ico" width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
      <rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="2"/>
      <path d="M3 9h18" stroke="currentColor" stroke-width="2"/>
      <path d="M8 3v4M16 3v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
    </svg>
   
    <input id="month-input" type="month" name="month"
           value="{{ $month }}" onchange="this.form.submit()" />
  </div>
</form>

  <a class="ms-btn ms-next"
     href="{{ route('admin.staffs.monthly', ['user'=>$user->id, 'month'=>$nextMonth]) }}">翌月 →</a>
</div>

    <script>
document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('month-input');
  if (!input) return;

  
  input.addEventListener('mousedown', (e) => {
    e.preventDefault();                
    try {
      if (typeof input.showPicker === 'function') {
        input.showPicker();            
      } else {
        input.focus(); input.click();  
      }
    } catch (_) {
      input.focus(); input.click();
    }
  });

  
  input.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      if (input.showPicker) input.showPicker();
    }
  });
});
</script>




  <div class="monthly-panel">
    <table class="monthly-table">
      <thead>
        <tr>
          <th>日付</th><th>出勤</th><th>退勤</th><th>休憩</th><th>合計</th><th>詳細</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($attendances as $a)
          <tr>
           @php
             $d = \Carbon\Carbon::parse($a->work_date);
             $youbi = ['日','月','火','水','木','金','土'][$d->dayOfWeek];
            @endphp
            <td>{{ $d->format('m/d') }}（{{ $youbi }}）</td>
            <td>{{ optional($a->clock_in)->format('H:i') ?? '--:--' }}</td>
            <td>{{ optional($a->clock_out)->format('H:i') ?? '--:--' }}</td>
            <td>{{ sprintf('%02d:%02d', intdiv($a->breaks_sum_duration_minutes ?? 0,60), ($a->breaks_sum_duration_minutes ?? 0)%60) }}</td>
            <td>
              @php
                $in  = $a->clock_in; $out = $a->clock_out;
                $mins = ($in && $out) ? $out->diffInMinutes($in) - ($a->breaks_sum_duration_minutes ?? 0) : 0;
              @endphp
              {{ $in && $out ? sprintf('%02d:%02d', intdiv(max($mins,0),60), max($mins,0)%60) : '--:--' }}
            </td>
            <td><a class="link" href="{{ route('admin.attendance.show', $a->id) }}">詳細</a></td>
          </tr>
        @empty
          <tr><td colspan="6" style="text-align:center; color:#6b7280;">データがありません</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="monthly-actions">
    
   <a class="btn btn-black"
   href="{{ route('admin.staffs.monthly.csv', ['user' => $user->id, 'month' => $month]) }}">
  CSV出力
</a>


  </div>
</div>
@endsection




