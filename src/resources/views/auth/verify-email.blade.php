@extends('layouts.user')

@section('title', 'メール認証')

@section('content')
<div class="verify">
  <h1>メール認証</h1>
  <p>
    登録メールアドレスに確認メールを送信しました。<br>
    メール内のリンクをクリックして認証を完了してください。
  </p>

  @if (session('status') == 'verification-link-sent')
    <div class="flash-success">確認メールを再送しました。数分お待ちください。</div>
  @endif

  <form method="POST" action="{{ route('verification.send') }}" class="mt-6">
    @csrf
    <button type="submit" class="btn btn-black w-full">認証メールを再送する</button>
  </form>

  <form method="POST" action="{{ route('logout') }}" class="mt-3">
    @csrf
    <button type="submit" class="btn btn-ghost w-full">ログアウト</button>
  </form>
</div>
@endsection

