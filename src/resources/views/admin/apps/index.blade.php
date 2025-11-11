@extends('layouts.admin')

@push('styles')
  
  <link rel="stylesheet" href="{{ asset('css/admin-apps.css') }}?v={{ filemtime(public_path('css/admin-apps.css')) }}">
  
  
@endpush

@section('content')
<div class="admin-page">
  <h1 class="u-title-bar">申請一覧</h1>

  <div class="apps-tabs">
    <a href="{{ route('admin.apps.index',['status'=>'pending']) }}"
       class="apps-tab {{ $status==='pending' ? 'is-active' : '' }}">承認待ち</a>
    <a href="{{ route('admin.apps.index',['status'=>'approved']) }}"
       class="apps-tab {{ $status==='approved' ? 'is-active' : '' }}">承認済み</a>
    
  </div>

  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>状態</th><th>名前</th><th>対象日時</th><th>申請理由</th><th>申請日時</th><th>詳細</th>
        </tr>
      </thead>
      <tbody>
        @foreach($apps as $app)
        <tr>
          <td>{{ ['承認待ち','承認済み',][$app->status] ?? '' }}</td>
          <td>{{ $app->user->name ?? '' }}</td>
          <td>{{ optional($app->attendance->work_date)->format('Y/m/d') }}</td>
          <td class="u-ellipsis">{{ $app->note }}</td>
          <td>{{ $app->created_at->format('Y/m/d') }}</td>
          <td><a class="link" href="{{ route('admin.apps.show', $app->id) }}">詳細</a></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="u-pager">{{ $apps->withQueryString()->links() }}</div>
</div>
@endsection

