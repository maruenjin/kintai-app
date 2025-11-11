@extends('layouts.user')
@section('title','勤怠詳細')

@push('styles')
  <link rel="stylesheet"
        href="{{ asset('css/attendance-show.css') }}?v={{ filemtime(public_path('css/attendance-show.css')) }}">
@endpush

@section('content')
<div class="u-container">
  <div class="detail-block">
    <h1 class="u-page-title u-title-bar">勤怠詳細</h1>

    @php
      $hasPending = isset($hasPending)
        ? (bool) $hasPending
        : \App\Models\AttendanceApplication::where('attendance_id', $attendance->id)
            ->where('status', 0)
            ->exists();

      $breaks = $attendance->breaks ?? collect();
      $b1 = $breaks[0] ?? null;
      $b2 = $breaks[1] ?? null;
    @endphp

    @if (session('status'))
      <p class="u-success">{{ session('status') }}</p>
    @endif

   

    @if ($hasPending)
     
      <div class="detail-card">
        <table class="detail-table">
          <tr>
            <th>名前</th>
            <td>{{ optional($attendance->user)->name ?? '—' }}</td>
          </tr>
          <tr>
            <th>日付</th>
            <td>{{ optional($attendance->work_date)->isoFormat('YYYY年M月D日（ddd）') ?? '—' }}</td>
          </tr>
          <tr>
            <th>出勤・退勤</th>
            <td class="range">
              <span class="chip">{{ optional($attendance->clock_in)->format('H:i') ?? '—' }}</span>
              <span class="tilde">〜</span>
              <span class="chip">{{ optional($attendance->clock_out)->format('H:i') ?? '—' }}</span>
            </td>
          </tr>
          <tr>
            <th>休憩</th>
            <td class="range">
              <span class="chip">{{ optional($b1->break_start ?? null)->format('H:i') ?? '—' }}</span>
              <span class="tilde">〜</span>
              <span class="chip">{{ optional($b1->break_end ?? null)->format('H:i') ?? '—' }}</span>
            </td>
          </tr>
          <tr>
            <th>休憩2</th>
            <td class="range">
              <span class="chip">{{ optional($b2->break_start ?? null)->format('H:i') ?? '—' }}</span>
              <span class="tilde">〜</span>
              <span class="chip">{{ optional($b2->break_end ?? null)->format('H:i') ?? '—' }}</span>
            </td>
          </tr>
          <tr>
            <th>備考</th>
            <td>
              <span class="note-text">{{ $attendance->note ?: '—' }}</span>
            </td>
          </tr>
        </table>
      </div>

      <p class="note-disabled outside">※承認待ちのため修正はできません。</p>

    @else
    
      <form method="POST" action="{{ route('user.apps.store', $attendance) }}">
        @csrf

        <div class="detail-card">
          <table class="detail-table">
            <tr>
              <th>名前</th>
              <td>{{ optional($attendance->user)->name ?? '—' }}</td>
            </tr>
            <tr>
              <th>日付</th>
              <td>{{ optional($attendance->work_date)->isoFormat('YYYY年M月D日（ddd）') ?? '—' }}</td>
            </tr>

          
            <tr>
              <th>出勤・退勤</th>
              <td>
                <div class="form-range">
                  <input type="time"
                         name="clock_in"
                         class="input-time"
                         value="{{ old('clock_in', optional($attendance->clock_in)->format('H:i')) }}">
                  <span class="form-tilde">〜</span>
                  <input type="time"
                         name="clock_out"
                         class="input-time"
                         value="{{ old('clock_out', optional($attendance->clock_out)->format('H:i')) }}">
                </div>
                @error('clock_in')  <div class="u-error">{{ $message }}</div> @enderror
                @error('clock_out') <div class="u-error">{{ $message }}</div> @enderror
              </td>
            </tr>

           
            <tr>
              <th>休憩</th>
              <td>
                <div class="form-range">
                  <input type="time"
                         name="break1_start"
                         class="input-time"
                         value="{{ old('break1_start', optional($b1->break_start ?? null)->format('H:i')) }}">
                  <span class="form-tilde">〜</span>
                  <input type="time"
                         name="break1_end"
                         class="input-time"
                         value="{{ old('break1_end', optional($b1->break_end ?? null)->format('H:i')) }}">
                </div>
                @error('break1_start') <div class="u-error">{{ $message }}</div> @enderror
                @error('break1_end')   <div class="u-error">{{ $message }}</div> @enderror
              </td>
            </tr>

          
            <tr>
              <th>休憩2</th>
              <td>
                <div class="form-range">
                  <input type="time"
                         name="break2_start"
                         class="input-time"
                         value="{{ old('break2_start', optional($b2->break_start ?? null)->format('H:i')) }}">
                  <span class="form-tilde">〜</span>
                  <input type="time"
                         name="break2_end"
                         class="input-time"
                         value="{{ old('break2_end', optional($b2->break_end ?? null)->format('H:i')) }}">
                </div>
                @error('break2_start') <div class="u-error">{{ $message }}</div> @enderror
                @error('break2_end')   <div class="u-error">{{ $message }}</div> @enderror
              </td>
            </tr>

           
            <tr>
              <th>備考</th>
              <td>
                <textarea name="reason"
                          class="note-text"
                          rows="2"
                          >{{ old('reason') }}</textarea>
                @error('reason') <div class="u-error">{{ $message }}</div> @enderror
              </td>
            </tr>
          </table>
        </div>

       
        <div class="detail-footer">
          <button type="submit" class="btn-black btn-lg">修正</button>
        </div>
      </form>
    @endif

  </div> 
</div>
@endsection





