@extends('layouts.user')
@section('title','申請一覧')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/apps.css') }}?v={{ filemtime(public_path('css/apps.css')) }}">
@endpush

@section('content')
<div class="u-container">
  <h1 class="u-page-title u-title-bar">申請一覧</h1>

  <div class="table-wrap">
    <h2>承認待ち</h2>
    <table class="att-table">
      <thead>
        <tr><th>日付</th><th>区分</th><th>備考</th><th></th></tr>
      </thead>
      <tbody>
      @forelse($pending as $a)
        <tr>
          <td>{{ optional($a->work_date)->format('Y/m/d') }}</td>
          <td>修正申請</td>
          <td>{{ \Illuminate\Support\Str::limit($a->note, 30) }}</td>
          <td><a class="u-link" href="{{ route('user.attendance.show', $a->attendance_id) }}">詳細</a></td>
        </tr>
      @empty
        <tr><td colspan="4">承認待ちはありません</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>

  <div class="table-wrap mt-24">
    <h2>承認済み</h2>
    <table class="att-table">
      <thead>
        <tr><th>日付</th><th>区分</th><th>備考</th><th></th></tr>
      </thead>
      <tbody>
      @forelse($approved as $a)
        <tr>
          <td>{{ optional($a->work_date)->format('Y/m/d') }}</td>
          <td>修正申請</td>
          <td>{{ \Illuminate\Support\Str::limit($a->note, 30) }}</td>
          <td><a class="u-link" href="{{ route('user.attendance.show', $a->attendance_id) }}">詳細</a></td>
        </tr>
      @empty
        <tr><td colspan="4">承認済みはありません</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection


