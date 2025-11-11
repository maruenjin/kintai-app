@extends('layouts.user')
@section('title','修正申請')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/apps.css') }}?v={{ filemtime(public_path('css/apps.css')) }}">
@endpush

@section('content')
<div class="u-container">
  <div class="detail-block" style="max-width:640px; margin:0 auto;">
    <h1 class="u-page-title u-title-bar">勤怠詳細</h1>

    <form method="POST" action="{{ route('user.apps.store', $attendance) }}">
      @csrf

      <div class="card">
        <table class="form-table">
          <tr>
            <th>名前</th>
            <td>{{ optional($attendance->user)->name }}</td>
          </tr>

          <tr>
            <th>日付</th>
            <td>{{ optional($attendance->work_date)->format('Y年n月j日（D）') }}</td>
          </tr>

          <tr>
            <th>出勤・退勤</th>
            <td class="form-range">
              <input class="input-dt" type="datetime-local" name="clock_in"
                     value="{{ old('clock_in', optional($attendance->clock_in)->format('Y-m-d\TH:i')) }}">
              <span class="form-tilde">〜</span>
              <input class="input-dt" type="datetime-local" name="clock_out"
                     value="{{ old('clock_out', optional($attendance->clock_out)->format('Y-m-d\TH:i')) }}">
            </td>
          </tr>

          @php
            $sorted = ($attendance->breaks ?? collect())->sortBy('break_start')->values();
            $b0 = optional($sorted->get(0));
            $b1 = optional($sorted->get(1));
            $b0_start = $b0->break_start ? $b0->break_start->format('Y-m-d\TH:i') : '';
            $b0_end   = $b0->break_end   ? $b0->break_end->format('Y-m-d\TH:i')   : '';
            $b1_start = $b1->break_start ? $b1->break_start->format('Y-m-d\TH:i') : '';
            $b1_end   = $b1->break_end   ? $b1->break_end->format('Y-m-d\TH:i')   : '';
          @endphp

          {{-- 休憩（1本目） --}}
          <tr>
            <th>休憩</th>
            <td class="form-range">
              <input class="input-dt" type="datetime-local" name="breaks[0][start]"
                     value="{{ old('breaks.0.start', $b0_start) }}">
              <span class="form-tilde">〜</span>
              <input class="input-dt" type="datetime-local" name="breaks[0][end]"
                     value="{{ old('breaks.0.end', $b0_end) }}">
            </td>
          </tr>

          
          <tr>
            <th>休憩2</th>
            <td class="form-range">
              <input class="input-dt" type="datetime-local" name="breaks[1][start]"
                     value="{{ old('breaks.1.start', $b1_start) }}">
              <span class="form-tilde">〜</span>
              <input class="input-dt" type="datetime-local" name="breaks[1][end]"
                     value="{{ old('breaks.1.end', $b1_end) }}">
            </td>
          </tr>

          <tr>
            <th>備考</th>
            <td>
              <input class="input-text" type="text" name="note"
                     value="{{ old('note') }}" placeholder="例）電車遅延のため">
            </td>
          </tr>
        </table>

        @if ($errors->any())
          <div class="u-errors">
            @foreach ($errors->all() as $msg)
              <div class="u-error">{{ $msg }}</div>
            @endforeach
          </div>
        @endif

        <div class="btn-area">
          <button class="btn-black" type="submit">修正</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection



