@extends('layouts.user')

@push('styles')
<link rel="stylesheet"
  href="{{ asset('css/user-apps.css') }}?v={{ file_exists(public_path('css/user-apps.css')) ? filemtime(public_path('css/user-apps.css')) : 1 }}">
@endpush

@section('title','申請一覧')

@section('content')
<div class="page-user-apps">
  <h1 class="u-title-bar">申請一覧</h1>

 
  <div class="apps-head">
    <div class="apps-head__tabs">
      <a href="{{ route('user.apps.index', ['status'=>'pending']) }}"
         class="tab {{ $status==='pending' ? 'is-active' : '' }}">承認待ち</a>
      <a href="{{ route('user.apps.index', ['status'=>'approved']) }}"
         class="tab {{ $status==='approved' ? 'is-active' : '' }}">承認済み</a>
    </div>
    <div class="apps-head__divider"></div>
  </div>

  
  <div class="app-card">
    <table class="apps-table">
      <thead>
        <tr>
          <th>状態</th>
          <th>名前</th>
          <th>対象日</th>
          <th>申請理由</th>
          <th>申請日時</th>
          <th class="col-action">詳細</th>
        </tr>
      </thead>
      <tbody>
      @php $me = auth()->user(); @endphp
      @forelse($apps as $app)
        <tr>
          <td>{{ $status==='pending' ? '承認待ち' : '承認済み' }}</td>
          <td>{{ $me ? $me->name : '—' }}</td>
          <td>{{ \Carbon\Carbon::parse($app->work_date)->format('Y/m/d') }}</td>
          <td>{{ $app->note ?: '—' }}</td>
          <td>{{ optional($app->created_at)->format('Y/m/d') }}</td>
          <td class="col-action">
            <a class="u-link" href="{{ route('user.attendance.show', $app->attendance_id) }}">詳細</a>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="empty">
            {{ $status==='pending' ? '承認待ちはありません' : '承認済みはありません' }}
          </td>
        </tr>
      @endforelse
      </tbody>
    </table>

    
    <div class="mt-4">
      {{ $apps->links() }}
    </div>
  </div>
</div>
@endsection


