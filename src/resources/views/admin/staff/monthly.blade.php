@extends('layouts.admin')
@section('title','月次')

@section('content')
<div class="container">
  <h1>{{ $user->name }} さんの月次</h1>

  <div style="margin:8px 0;">
    <a href="{{ route('admin.staff.monthly',['user'=>$user->id,'ym'=>$prev]) }}">＜ 前月</a>
    <b style="margin:0 12px">{{ $ym }}</b>
    <a href="{{ route('admin.staff.monthly',['user'=>$user->id,'ym'=>$next]) }}">翌月 ＞</a>
  </div>

  <div style="margin:8px 0;">
    <a href="{{ route('admin.staff.csv',['user'=>$user->id,'ym'=>$ym]) }}" class="btn">CSV出力</a>
  </div>

  <table style="width:100%;border-collapse:collapse;background:#fff;border:1px solid #eee">
    <thead>
      <tr style="background:#f7f7f7">
        <th style="padding:8px;border-bottom:1px solid #eee">日付</th>
        <th style="padding:8px;border-bottom:1px solid #eee">出勤</th>
        <th style="padding:8px;border-bottom:1px solid #eee">退勤</th>
        <th style="padding:8px;border-bottom:1px solid #eee">休憩合計</th>
        <th style="padding:8px;border-bottom:1px solid #eee">勤務合計</th>
        <th style="padding:8px;border-bottom:1px solid #eee">実働</th>
        <th style="padding:8px;border-bottom:1px solid #eee">備考</th>
      </tr>
    </thead>
    <tbody>
      @foreach($recs as $r)
        <tr>
          <td style="padding:8px;border-bottom:1px solid #f3f4f6">{{ $r->work_date }}</td>
          <td style="padding:8px;border-bottom:1px solid #f3f4f6">{{ optional($r->clock_in)->format('H:i') }}</td>
          <td style="padding:8px;border-bottom:1px solid #f3f4f6">{{ optional($r->clock_out)->format('H:i') }}</td>
          <td style="padding:8px;border-bottom:1px solid #f3f4f6">{{ $r->breakMinutes() }}分</td>
          <td style="padding:8px;border-bottom:1px solid #f3f4f6">{{ $r->workMinutes() }}分</td>
          <td style="padding:8px;border-bottom:1px solid #f3f4f6">{{ max(0,$r->workMinutes()-$r->breakMinutes()) }}分</td>
          <td style="padding:8px;border-bottom:1px solid #f3f4f6">{{ $r->note }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <div style="margin-top:12px;">
    <b>合計</b>：勤務 {{ intdiv($totalWork,60) }}時間{{ $totalWork%60 }}分 /
    休憩 {{ intdiv($totalBreak,60) }}時間{{ $totalBreak%60 }}分 /
    実働 {{ intdiv($totalNet,60) }}時間{{ $totalNet%60 }}分
  </div>
</div>
@endsection
