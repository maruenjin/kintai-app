@extends('layouts.admin')
@section('title', '日次勤怠一覧')

@section('content')
<div class="admin-daily">

  <h1 class="daily-title">
    <span class="bar"></span>
    {{ $targetDate->format('Y年n月j日') }}の勤怠
  </h1>

 

<div class="daily-row">
  <div class="daily-pill">
    <a class="pill-btn prev"
       href="{{ route('admin.attendance.list', ['date'=>$prevDate->toDateString()]) }}">前日</a>

    <form method="GET" action="{{ route('admin.attendance.list') }}" class="pill-date">
         <button type="button" class="calendar-btn" aria-label="カレンダーを開く"></button>
     <input class="js-date no-native-icon" type="date" name="date" value="{{ $targetDate->toDateString() }}">
    </form>

    <a class="pill-btn next"
       href="{{ route('admin.attendance.list', ['date'=>$nextDate->toDateString()]) }}">翌日</a>
  </div>
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const dateInput = document.querySelector('.js-date');
    if (dateInput) dateInput.addEventListener('change', () => dateInput.form.submit());

    const openBtn = document.querySelector('.calendar-btn');
    if (openBtn && dateInput && typeof dateInput.showPicker === 'function') {
      openBtn.addEventListener('click', () => dateInput.showPicker());
    } else if (openBtn && dateInput) {
      openBtn.addEventListener('click', () => dateInput.focus());
    }
  });
</script>
@endpush


  
  <div class="table-wrap">
    <table class="table">
       <colgroup>
      <col class="col-name">  
      <col>
      <col>
      <col>
      <col>
      <col>
    </colgroup>

      <thead>
        <tr>
          <th>名前</th>
          <th>出勤</th>
          <th>退勤</th>
          <th>休憩</th>
          <th>合計</th>
          <th>詳細</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($rows as $r)
          <tr>
            <td>{{ $r->user->name }}</td>
            <td>{{ $r->clock_in ? \Carbon\Carbon::parse($r->clock_in)->format('H:i') : '' }}</td>
            <td>{{ $r->clock_out ? \Carbon\Carbon::parse($r->clock_out)->format('H:i') : '' }}</td>
            <td>{{ sprintf('%02d:%02d', intdiv($r->breaks_sum_duration_minutes, 60), $r->breaks_sum_duration_minutes % 60) }}</td>
            <td>{{ $r->worked_hhmm ?? '' }}</td>
            <td><a class="link" href="{{ route('admin.attendance.show', $r->id) }}">詳細</a></td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="empty">データがありません</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection



