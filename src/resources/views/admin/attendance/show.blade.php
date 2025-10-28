@extends('layouts.admin')
@section('title','勤怠詳細')

@section('content')
<div class="container">
<div class="card card-lg">
  <h2 class="mb-6">勤怠詳細</h2>

  @if(session('status'))
    <div class="alert success">{{ session('status') }}</div>
  @endif
  @if ($errors->any())
    <div class="alert error">
      <ul class="list-disc pl-5">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.attendance.update', $attendance) }}" class="form-grid">
    @csrf
    @method('PUT')

    <div class="grid-row">
      <label>名前</label>
      <div>{{ $attendance->user->name }}</div>
    </div>

    <div class="grid-row">
      <label>日付</label>
      <div>{{ $attendance->work_date->format('Y年n月j日') }}</div>
    </div>

    <div class="grid-row">
      <label>出勤・退勤</label>
      <div class="time-pair">
        <input type="time" name="clock_in"  value="{{ old('clock_in', optional($attendance->clock_in)->format('H:i')) }}">
        <span class="tilde">~</span>
        <input type="time" name="clock_out" value="{{ old('clock_out', optional($attendance->clock_out)->format('H:i')) }}">
      </div>
    </div>

    
     @php
      $b1 = $attendance->breaks[0] ?? null;
      $b2 = $attendance->breaks[1] ?? null;

      $b1s = $b1 && $b1->break_start ? $b1->break_start->format('H:i') : '';
      $b1e = $b1 && $b1->break_end   ? $b1->break_end->format('H:i')   : '';
      $b2s = $b2 && $b2->break_start ? $b2->break_start->format('H:i') : '';
      $b2e = $b2 && $b2->break_end   ? $b2->break_end->format('H:i')   : '';
    @endphp

    <div class="grid-row">
      <label>休憩</label>
      <div class="time-pair">
       <input type="time" name="b1_start" value="{{ old('b1_start', $b1s) }}">
       <span class="tilde">~</span>
       <input type="time" name="b1_end"   value="{{ old('b1_end', $b1e) }}">
     </div>
    </div>

    <div class="grid-row">
     <label>休憩2</label>
     <div class="time-pair">
      <input type="time" name="b2_start" value="{{ old('b2_start', $b2s) }}">
      <span class="tilde">~</span>
      <input type="time" name="b2_end"   value="{{ old('b2_end', $b2e) }}">
    </div>
   </div>


    <div class="grid-row">
      <label>備考</label>
      <textarea name="note" rows="2">{{ old('note', $attendance->note) }}</textarea>

    </div>

    <div class="form-actions">
      
      <button type="submit" class="btn-primary">修正</button>
    </div>
  </form>
</div>
@endsection
