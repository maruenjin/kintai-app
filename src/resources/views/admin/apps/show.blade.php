@extends('layouts.admin')
@section('title','申請詳細')

@section('content')
<div class="container">
  <h1>申請詳細 #{{ $app->id }}</h1>

  @if ($errors->any())
    <div class="form-error">
      <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif
  @if (session('status'))
    <div class="flash-success">{{ session('status') }}</div>
  @endif

  <div class="card" style="background:#fff;border:1px solid #eee;border-radius:8px;padding:12px">
    <p><b>申請者:</b> {{ $app->user->name ?? '-' }}</p>
    <p><b>対象勤怠:</b> #{{ $app->attendance_id }}（{{ optional($app->attendance)->work_date }}）</p>
    <p><b>申請内容:</b></p>
    <ul style="margin:6px 0 12px 18px;">
      <li>日付: {{ $app->work_date ?? '—' }}</li>
      <li>出勤: {{ $app->clock_in ? \Carbon\Carbon::parse($app->clock_in)->format('Y-m-d H:i') : '—' }}</li>
      <li>退勤: {{ $app->clock_out ? \Carbon\Carbon::parse($app->clock_out)->format('Y-m-d H:i') : '—' }}</li>
      <li>備考: {{ $app->note ?? '—' }}</li>
    </ul>
    <p><b>状態:</b>
      @if($app->status===0) 承認待ち
      @elseif($app->status===1) 承認（{{ $app->approved_at }} by #{{ $app->approved_by }}）
      @else 却下（{{ $app->approved_at }} by #{{ $app->approved_by }}）
      @endif
    </p>
  </div>

  @if($app->status === 0)
  <div style="display:flex;gap:12px;margin-top:12px;">
    <form method="POST" action="{{ route('admin.apps.approve',$app) }}">
      @csrf @method('PUT')
      <input type="text" name="manager_comment" placeholder="管理者コメント（任意）">
      <button type="submit" class="btn">承認</button>
    </form>

    <form method="POST" action="{{ route('admin.apps.reject',$app) }}">
      @csrf @method('PUT')
      <input type="text" name="manager_comment" placeholder="管理者コメント（任意）">
      <button type="submit" class="btn">却下</button>
    </form>
  </div>
  @endif

  <div style="margin-top:12px;">
    <a href="{{ route('admin.apps.index') }}">← 一覧へ戻る</a>
  </div>
</div>
@endsection
