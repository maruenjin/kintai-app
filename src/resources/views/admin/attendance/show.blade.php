
@extends('layouts.admin')
@section('title','勤怠詳細')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-attendance.css') }}?v={{ filemtime(public_path('css/admin-attendance.css')) }}">
<style>
  :root{ --card-w:900px; }
  .admin-detail-page{ background:#f5f6f8; }
  .detail-shell{ width:var(--card-w) !important; margin:0 auto !important; }
  .detail-title{ width:var(--card-w) !important; margin:0 auto 12px !important; padding-left:0 !important; position:relative; text-align:left; }
  .detail-title.u-title-bar::before{
    content:""; position:absolute; left:-12px; top:50%; transform:translateY(-50%);
    width:4px; height:24px; background:#111; border-radius:2px;
  }
  .detail-card{
    width:var(--card-w) !important; margin:0 auto !important;
    background:#fff; border-radius:12px; box-shadow:0 10px 24px rgba(0,0,0,.06); padding:24px 28px;
  }
  .outside-btn{ width:var(--card-w) !important; margin:12px auto 0 !important; display:flex; justify-content:flex-end; }
  /* 行内エラー表示 */
  .u-error{ color:#b91c1c; font-size:12px; margin-top:6px; }
  .row-error td{ padding-top:0; padding-bottom:12px; }
  .input-time[aria-invalid="true"], .input-text[aria-invalid="true"]{
    border-color:#b91c1c; box-shadow:0 0 0 3px rgba(185,28,28,.12);
  }
</style>
@endpush

@section('content')
@php
  use Carbon\Carbon;
  $rows = collect($attendance->breaks ?? [])->sortBy('break_start')->values();
  $b0 = $rows->get(0);
  $b1 = $rows->get(1);

  $dateText = $attendance->work_date
      ? Carbon::parse($attendance->work_date)->format('Y年n月j日') : '';

  $clockIn  = $attendance->clock_in
      ? Carbon::parse($attendance->clock_in)->format('H:i') : '';
  $clockOut = $attendance->clock_out
      ? Carbon::parse($attendance->clock_out)->format('H:i') : '';

  $b0s = ($b0 && $b0->break_start)
      ? Carbon::parse($b0->break_start)->format('H:i') : '';
  $b0e = ($b0 && $b0->break_end)
      ? Carbon::parse($b0->break_end)->format('H:i') : '';

  $b1s = ($b1 && $b1->break_start)
      ? Carbon::parse($b1->break_start)->format('H:i') : '';
  $b1e = ($b1 && $b1->break_end)
      ? Carbon::parse($b1->break_end)->format('H:i') : '';
@endphp


<div class="admin-detail-page">
  <div class="detail-shell">
    <h1 class="detail-title u-title-bar">勤怠詳細</h1>

    

    <div class="detail-card">
      <form id="attendance-form" method="POST" action="{{ route('admin.attendance.update', $attendance) }}">
        @csrf
        @method('PUT')

        <table class="detail-table">
          <tbody>
            <tr>
              <th>名前</th>
              <td>{{ optional($attendance->user)->name }}</td>
            </tr>

            <tr>
              <th>日付</th>
              <td>{{ $dateText }}</td>
            </tr>

           
            <tr>
              <th>出勤・退勤</th>
              <td class="form-range">
               <input class="input-time" type="time" name="clock_in_time"
       value="{{ old('clock_in_time', $clockIn) }}"
       @error('clock_in_time') aria-invalid="true" aria-describedby="err-clock-in" @enderror>

                <span class="form-tilde">〜</span>
                <input class="input-time" type="time" name="clock_out_time"
       value="{{ old('clock_out_time', $clockOut) }}"
       @error('clock_out_time') aria-invalid="true" aria-describedby="err-clock-out" @enderror>
              </td>
            </tr>
            <tr class="row-error"><td></td>
              <td>
                @error('clock_in_time')  <div id="err-clock-in"  class="u-error">{{ $message }}</div> @enderror
                @error('clock_out_time') <div id="err-clock-out" class="u-error">{{ $message }}</div> @enderror
              </td>
            </tr>

            
            <tr>
              <th>休憩</th>
              <td class="form-range">
               <input class="input-time" type="time" name="breaks[0][start]"
       value="{{ old('breaks.0.start', $b0s) }}"
       @error('breaks.0.start') aria-invalid="true" aria-describedby="err-b0s" @enderror>
 
                <span class="form-tilde">〜</span>
              <input class="input-time" type="time" name="breaks[0][end]"
       value="{{ old('breaks.0.end', $b0e) }}"
       @error('breaks.0.end') aria-invalid="true" aria-describedby="err-b0e" @enderror> 
              </td>
            </tr>
            <tr class="row-error">
  <td></td>
  <td>
    @if ($errors->has('breaks.0.start') || $errors->has('breaks.0.end'))
      <div class="u-error">
        {{ $errors->first('breaks.0.end') ?: $errors->first('breaks.0.start') }}
      </div>
    @endif
  </td>
</tr>


           
            <tr>
              <th>休憩2</th>
              <td class="form-range">
               <input class="input-time" type="time" name="breaks[1][start]"
       value="{{ old('breaks.1.start', $b1s) }}"
       @error('breaks.1.start') aria-invalid="true" aria-describedby="err-b1s" @enderror>

                <span class="form-tilde">〜</span>
                <input class="input-time" type="time" name="breaks[1][end]"
       value="{{ old('breaks.1.end', $b1e) }}"
       @error('breaks.1.end') aria-invalid="true" aria-describedby="err-b1e" @enderror>
              </td>
            </tr>
            <tr class="row-error">
  <td></td>
  <td>
    @if ($errors->has('breaks.1.start') || $errors->has('breaks.1.end'))
      <div class="u-error">
        {{ $errors->first('breaks.1.end') ?: $errors->first('breaks.1.start') }}
      </div>
    @endif
  </td>
</tr>


            
            <tr>
              <th>備考</th>
              <td>
                <input id="note" class="input-text" type="text" name="note"
                       value="{{ old('note', $attendance->note) }}"
                       @error('note') aria-invalid="true" aria-describedby="err-note" @enderror>
              </td>
            </tr>
           <tr class="row-error">
  <td></td>
  <td>
    @if ($errors->has('note'))
      <div class="u-error">{{ $errors->first('note') }}</div>
    @endif
  </td>
</tr>

          </tbody>
        </table>
      </form>
    </div>

    
    <div class="outside-btn">
      <button class="btn-black" type="submit" form="attendance-form">修正</button>
    </div>
  </div>
</div>
@endsection







