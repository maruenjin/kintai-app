@extends('layouts.admin')
@section('title','スタッフ一覧')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-staffs.css') }}?v={{ filemtime(public_path('css/admin-staffs.css')) }}">
@endpush

@section('content')
  <div class="staff-page">
    <h1 class="staff-title u-title-bar">スタッフ一覧</h1>

    
    

    <div class="staff-panel">
      <table class="staff-table">
        <colgroup>
          <col style="width:36%;">
          <col style="width:44%;">
          <col style="width:20%;">
        </colgroup>
        <thead>
          <tr>
            <th>名前</th>
            <th>メールアドレス</th>
            <th>月次勤怠</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($users as $u)
            <tr>
              <td>{{ $u->name }}</td>
              <td>{{ $u->email }}</td>
              <td>
                <a class="link"
                   href="{{ route('admin.staffs.monthly', ['user'=>$u->id, 'month'=>$currentYm]) }}">
                  詳細
                </a>
              </td>
            </tr>
          @empty
            <tr><td colspan="3" style="text-align:center; color:#6b7280;">データがありません</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    
    @if(method_exists($users, 'links'))
      <div style="width:var(--staff-panel-w); margin:12px auto 0;">{{ $users->links() }}</div>
    @endif
  </div>
@endsection


