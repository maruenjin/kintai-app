@extends('layouts.user')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}?v={{ filemtime(public_path('css/attendance-list.css')) }}">
@endpush

@section('title','勤怠一覧')

@section('content')
<div class="u-container">
  <div class="u-page-title">勤怠一覧</div>

  
  <div class="u-toolbar">
    <div class="u-month-picker" role="group" aria-label="月の切り替え">
      <a class="mp-btn mp-prev" href="{{ route('user.attendance.list',['ym'=>$prev]) }}">
        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M15 18l-6-6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <span>前月</span>
      </a>

      
      <button type="button" class="mp-ym" id="mpYmBtn" aria-expanded="false" aria-controls="mpPop" aria-label="年月を選択">
        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
          <rect x="3" y="5" width="18" height="16" rx="3" fill="none" stroke="currentColor" stroke-width="2"/>
          <path d="M8 3v4M16 3v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <span>{{ $ym }}</span>
      </button>

      <a class="mp-btn mp-next" href="{{ route('user.attendance.list',['ym'=>$next]) }}">
        <span>翌月</span>
        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M9 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </a>

      
      <div id="mpPop" class="mp-pop" hidden>
        <div class="mp-head">
          <button type="button" class="mp-year-btn" id="mpYearPrev" aria-label="前年">‹</button>
          <div class="mp-year" id="mpYearLabel"></div>
          <button type="button" class="mp-year-btn" id="mpYearNext" aria-label="翌年">›</button>
        </div>
        <div class="mp-grid">
          @for($m=1; $m<=12; $m++)
            <button type="button" class="mp-cell" data-month="{{ sprintf('%02d',$m) }}">{{ $m }}</button>
          @endfor
        </div>
      </div>
    </div>
  </div>

  <div class="table-wrap">
    <table class="att-table">
      <thead>
        <tr>
          <th>日付</th>
          <th>出勤</th>
          <th>退勤</th>
          <th>休憩</th>
          <th>合計</th>
          <th>詳細</th>
        </tr>
      </thead>
      <tbody>
      @foreach($days as $d)
 @php
  /** @var \App\Models\Attendance|null $a */
  $a = $d['attendance'] ?? null;

  $toHm = function ($minutes) {
      $min = max(0, (int)$minutes);
      return sprintf('%d:%02d', intdiv($min, 60), $min % 60);
  };

  
  $totalMin = $a && method_exists($a,'workedMinutes')  ? (int)$a->workedMinutes()  : 0;

  
  $breakMin = $a && method_exists($a,'breakMinutes') ? (int)$a->breakMinutes() : 0;
@endphp

  <tr>
    <td class="col-date">{{ \Carbon\Carbon::parse($d['date'])->isoFormat('MM/DD(ddd)') }}</td>
    <td class="col-time">{{ $a ? optional($a->clock_in)->format('H:i')  : '—' }}</td>
    <td class="col-time">{{ $a ? optional($a->clock_out)->format('H:i') : '—' }}</td>

    
    <td class="col-rest">
      @if($a) {{ $toHm($breakMin) }} @else — @endif
    </td>

    
    <td class="col-total">
       @if($a) {{ $toHm($totalMin) }} @else — @endif
    </td>

    <td class="col-action">
      @if($a)
        <a class="u-link" href="{{ route('user.attendance.show', $a) }}">詳細</a>
      @else
        <span class="u-link -muted">—</span>
      @endif
    </td>
  </tr>
@endforeach

      </tbody>
    </table>
  </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', () => {
  const picker   = document.querySelector('.u-month-picker');
  const btn      = document.getElementById('mpYmBtn');
  const pop      = document.getElementById('mpPop');
  const yearLbl  = document.getElementById('mpYearLabel');
  const prevYBtn = document.getElementById('mpYearPrev');
  const nextYBtn = document.getElementById('mpYearNext');

  let [curYear, curMonth] = '{{ $ym }}'.split('-').map(x => parseInt(x, 10));
  let viewYear = curYear;

  const openPop = () => {
    yearLbl.textContent = String(viewYear);
    pop.hidden = false;
    btn.setAttribute('aria-expanded', 'true');
    document.addEventListener('click', onDocClick);
    document.addEventListener('keydown', onKeyDown);
  };
  const closePop = () => {
    pop.hidden = true;
    btn.setAttribute('aria-expanded', 'false');
    document.removeEventListener('click', onDocClick);
    document.removeEventListener('keydown', onKeyDown);
  };
  const onDocClick = (e) => {
    if (!pop.contains(e.target) && e.target !== btn) closePop();
  };
  const onKeyDown = (e) => {
    if (e.key === 'Escape') closePop();
  };

  btn.addEventListener('click', () => pop.hidden ? openPop() : closePop());
  prevYBtn.addEventListener('click', () => { viewYear--; yearLbl.textContent = String(viewYear); });
  nextYBtn.addEventListener('click', () => { viewYear++; yearLbl.textContent = String(viewYear); });

  pop.querySelectorAll('.mp-cell').forEach(cell => {
    cell.addEventListener('click', () => {
      const m = cell.dataset.month;     
      const ym = String(viewYear) + '-' + m;
      window.location.href = "{{ route('user.attendance.list') }}" + "?ym=" + ym;
    });
  });
});
</script>
@endsection




