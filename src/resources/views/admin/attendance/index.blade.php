@extends('layouts.admin')
@section('title','日次勤怠一覧')

@section('content')
<div class="admin-container">
  <div class="panel">
    @php
      $baseDate = \Carbon\Carbon::parse($date ?? now());
      $jTitle   = $baseDate->format('Y年n月j日');
    @endphp

    <h2 class="panel-title">{{ $jTitle }}の勤怠</h2>

    <form method="GET" action="{{ route('admin.attendance.list') }}" class="toolbar">
      <a class="btn-nav" href="{{ route('admin.attendance.list', ['date' => $baseDate->copy()->subDay()->toDateString()]) }}">← 前日</a>

      <div class="date-input">
        <input type="date" name="date" value="{{ $baseDate->toDateString() }}">
        <button class="btn-black" type="submit">表示</button>
      </div>

      <a class="btn-nav" href="{{ route('admin.attendance.list', ['date' => $baseDate->copy()->addDay()->toDateString()]) }}">翌日 →</a>
    </form>

    <div class="table-wrap">
      <table class="table">
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
@forelse ($items as $item)
  <tr>
    <td>{{ $item->user->name }}</td>
    <td>{{ optional($item->clock_in)->format('H:i') ?? '-' }}</td>
    <td>{{ optional($item->clock_out)->format('H:i') ?? '-' }}</td>
    <td>{{ $item->breakMinutes() ? gmdate('G:i', $item->breakMinutes()*60) : '-' }}</td>
    <td>{{ $item->workedMinutes() ? gmdate('G:i', $item->workedMinutes()*60) : '-' }}</td>
    <td>
      <a href="{{ route('admin.attendance.show', $item) }}">詳細</a>
    </td>
  </tr>
@empty
  <tr><td colspan="6" class="empty">データがありません</td></tr>
@endforelse
</tbody>



      </table>
    </div>
  </div>
</div>
@endsection
