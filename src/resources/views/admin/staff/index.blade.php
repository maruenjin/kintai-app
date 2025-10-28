@extends('layouts.admin')
@section('title','スタッフ一覧')

@section('content')
<div class="container">
  <h1>スタッフ一覧</h1>

  <form method="GET" action="{{ route('admin.staff.index') }}" style="margin:8px 0;">
    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="名前 or メールで検索">
    <button type="submit">検索</button>
  </form>

  <table style="width:100%;border-collapse:collapse;background:#fff;border:1px solid #eee">
    <thead>
      <tr style="background:#f7f7f7">
        <th style="padding:8px;border-bottom:1px solid #eee">ID</th>
        <th style="padding:8px;border-bottom:1px solid #eee">名前</th>
        <th style="padding:8px;border-bottom:1px solid #eee">メール</th>
        <th style="padding:8px;border-bottom:1px solid #eee"></th>
      </tr>
    </thead>
    <tbody>
      @forelse($staffs as $s)
        <tr>
          <td style="padding:8px;border-bottom:1px solid #f3f4f6">{{ $s->id }}</td>
          <td style="padding:8px;border-bottom:1px solid #f3f4f6">{{ $s->name }}</td>
          <td style="padding:8px;border-bottom:1px solid #f3f4f6">{{ $s->email }}</td>
          <td style="padding:8px;border-bottom:1px solid #f3f4f6">
            <a href="{{ route('admin.staff.monthly', ['user'=>$s->id]) }}">月次</a>
          </td>
        </tr>
      @empty
        <tr><td colspan="4" style="padding:12px;">データがありません</td></tr>
      @endforelse
    </tbody>
  </table>

  <div style="margin-top:12px;">{{ $staffs->withQueryString()->links() }}</div>
</div>
@endsection
