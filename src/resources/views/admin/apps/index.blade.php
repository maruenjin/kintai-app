@extends('layouts.admin')
@section('title','申請一覧')

@section('content')
<div class="container">
  <h1>申請一覧</h1>

  <div style="margin:8px 0;">
    <a href="{{ route('admin.apps.index',['status'=>'pending']) }}">承認待ち</a> |
    <a href="{{ route('admin.apps.index',['status'=>'approved']) }}">承認済み</a> |
    <a href="{{ route('admin.apps.index',['status'=>'rejected']) }}">却下</a>
  </div>

  @if(session('status')) <div class="flash-success">{{ session('status') }}</div> @endif

  <table style="width:100%;border-collapse:collapse;background:#fff;border:1px solid #eee">
    <thead>
      <tr style="background:#f7f7f7">
        <th style="padding:8px;border-bottom:1px solid #eee">ID</th>
        <th style="padding:8px;border-bottom:1px solid #eee">申請者</th>
        <th style="padding:8px;border-bottom:1px solid #eee">対象勤怠</th>
        <th style="padding:8px;border-bottom:1px solid #eee">状態</th>
        <th style="padding:8px;border-bottom:1px solid #eee"></th>
      </tr>
    </thead>
    <tbody>
      @forelse($apps as $app)
        <tr>
          <td style="padding:8px;border-bottom:1px solid #f3f4f6">#{{ $app->id }}</td>
          <td style="padding:8px;border-bottom:1px solid #f3f4f6">{{ $app->user->name ?? '-' }}</td>
          <td style="padding:8px;border-bottom:1px solid #f3f4f6">#{{ $app->attendance_id }}（{{ optional($app->attendance)->work_date }}）</td>
          <td style="padding:8px;border-bottom:1px solid #f3f4f6">
            @if($app->status===0) 承認待ち
            @elseif($app->status===1) 承認
            @else 却下
            @endif
          </td>
          <td style="padding:8px;border-bottom:1px solid #f3f4f6">
            <a href="{{ route('admin.apps.show',$app) }}">詳細</a>
          </td>
        </tr>
      @empty
        <tr><td colspan="5" style="padding:12px;">データがありません</td></tr>
      @endforelse
    </tbody>
  </table>

  <div style="margin-top:12px;">{{ $apps->withQueryString()->links() }}</div>
</div>
@endsection
