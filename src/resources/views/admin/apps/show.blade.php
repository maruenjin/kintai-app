@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-apps.css') }}?v={{ filemtime(public_path('css/admin-apps.css')) }}">
@endpush

@section('content')
<div class="admin-page apps-show"> 

  <div class="detail-shell">
    <h1 class="u-title-bar">勤怠詳細</h1>

    @if(session('status')) <p class="u-success">{{ session('status') }}</p> @endif
    @if($errors->any())    <p class="u-error">{{ $errors->first() }}</p>  @endif

    @php
      use Carbon\Carbon;
      $att  = $app->attendance;
      $user = $app->user;

      
      $wd  = $app->work_date ? Carbon::parse($app->work_date) : ($att->work_date ?? null);
      $in  = $app->clock_in  ? Carbon::parse($app->clock_in)  : $att->clock_in;
      $out = $app->clock_out ? Carbon::parse($app->clock_out) : $att->clock_out;
      $note = $app->note ?? $att->note;
      $breaks = $att->breaks ?? collect();
    @endphp

    <div class="detail-card">
      <table class="detail-table">
         <colgroup>
           <col class="col-label"><col class="col-value">
         </colgroup>
        <tr>
          <th>名前</th>
          <td>{{ $user->name ?? '—' }}</td>
        </tr>
        <tr>
          <th>日付</th>
          <td>{{ $wd ? $wd->format('Y年n月j日') : '—' }}</td>
        </tr>
        <tr>
          <th>出勤・退勤</th>
          <td class="range">
            <span class="chip">{{ optional($in)->format('H:i')  ?? '—' }}</span>
            <span class="tilde">〜</span>
            <span class="chip">{{ optional($out)->format('H:i') ?? '—' }}</span>
          </td>
        </tr>

        @forelse($breaks as $i => $b)
          <tr>
            <th>休憩{{ $i+1 }}</th>
            <td class="range">
              <span class="chip">{{ optional($b->break_start)->format('H:i') ?? '—' }}</span>
              <span class="tilde">〜</span>
              <span class="chip">{{ optional($b->break_end)->format('H:i')   ?? '—' }}</span>
            </td>
          </tr>
        @empty
          <tr>
            <th>休憩</th>
            <td><span class="chip chip--text">—</span></td>
          </tr>
        @endforelse

        <tr>
          <th>備考</th>
          <td>{{ $note ?: '—' }}</td>
        </tr>
      </table>
    </div>
  </div>

 
  <div class="detail-actions">
  @if($app->status === 0)
   <form method="POST" action="{{ url('/admin/applications/'.$app->id.'/approve') }}" class="inline">
  @csrf
  <button class="btn-black">承認</button>
</form>
  @elseif($app->status === 1)
    <span class="badge-approved">承認済み</span>
  @endif
</div>


</div>
@endsection



