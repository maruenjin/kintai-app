@extends('layouts.user')
@section('title', '打刻')

@section('content')
<div class="container-narrow ta-center">

  
  <div class="mt-10">
    <span class="chip">{{ $status }}</span>
    <div class="mt-4 text-date">
      {{ $today->isoFormat('YYYY年MM月DD日（ddd）') }}
    </div>
  </div>

  
  <div class="clock-hero" id="js-clock">--:--</div>

  
  <div class="mt-6">
    @csrf
    @if(!$attendance->clock_in)
      
      <form method="POST" action="{{ route('user.attendance.clockin') }}">
        @csrf
        <button class="btn-primary btn-lg" type="submit">出勤</button>
      </form>

    @elseif($isOnBreak)
      
      <form class="inline" method="POST" action="{{ route('user.attendance.breakout') }}">
        @csrf
        <button class="btn-primary btn-lg" type="submit">休憩戻</button>
      </form>

    @elseif($isWorking)
      
      <div class="btn-row mt-4">
        <form class="inline" method="POST" action="{{ route('user.attendance.clockout') }}">
          @csrf
          <button class="btn-black btn-lg" type="submit">退勤</button>
        </form>
        <form class="inline" method="POST" action="{{ route('user.attendance.breakin') }}">
          @csrf
          <button class="btn-ghost btn-lg" type="submit">休憩入</button>
        </form>
      </div>

    @else
      
      <p class="mt-4 text-muted">お疲れ様でした。</p>
    @endif
  </div>

</div>


<script>
  const el = document.getElementById('js-clock');
  function tick() {
    const d = new Date();
    const p = n => String(n).padStart(2,'0');
    el.textContent = `${p(d.getHours())}:${p(d.getMinutes())}`;
  }
  tick(); setInterval(tick, 1000);
</script>
@endsection

