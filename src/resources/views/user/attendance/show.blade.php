@extends('layouts.user')
@section('title','勤怠詳細')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/attendance-show.css') }}?v={{ filemtime(public_path('css/attendance-show.css')) }}">
@endpush

@section('content')
<div class="u-container">
  <div class="detail-block">
  <h1 class="u-page-title u-title-bar">勤怠詳細</h1>


  
  <div class="detail-card">
    <table class="detail-table">
  <tr>
    <th>名前</th>
    <td>{{ optional($attendance->user)->name ?? '—' }}</td>
  </tr>

  <tr>
    <th>日付</th>
    <td>{{ optional($attendance->work_date)->format('Y年n月j日（D）') ?? '—' }}</td>
  </tr>

  <tr>
    <th>出勤・退勤</th>
    <td class="range">
      <span class="chip">{{ optional($attendance->clock_in)->format('H:i') ?? '—' }}</span>
      <span class="tilde">〜</span>
      <span class="chip">{{ optional($attendance->clock_out)->format('H:i') ?? '—' }}</span>
    </td>
  </tr>

  @php $breaks = $attendance->breaks ?? collect(); @endphp

  @foreach($breaks as $i => $b)
  <tr>
    <th>休憩{{ $i+1 }}</th>
    <td class="range">
      <span class="chip">{{ optional($b->break_start)->format('H:i') ?? '—' }}</span>
      <span class="tilde">〜</span>
      <span class="chip">{{ optional($b->break_end)->format('H:i') ?? '—' }}</span>
    </td>
  </tr>
  @endforeach

  @if($breaks->isEmpty())
  <tr>
    <th>休憩</th>
    <td><span class="chip chip--text">—</span></td>
  </tr>
  @endif

  <tr>
    <th>備考</th>
    <td>
      <input type="text" name="note" class="note-input"
             value="{{ old('note', $attendance->note ?? '') }}"
             placeholder="例）電車遅延のため">
    </td>
  </tr>
</table>


<div class="btn-area">
  <a class="btn-black" href="{{ route('user.apps.create', $attendance) }}" style="display:inline-block;text-decoration:none;text-align:center;">修正</a>
</div>


  </div>

  <div class="back-link">
   
  </div>
</div>
@endsection

