@extends('layouts.user')

@section('title', 'メール認証')

@section('content')
<div class="verify">
  <p>
    登録していただいたメールアドレスに認証メールを送付しました。<br>
    メール認証を完了してください。
  </p>

  @if (session('status') == 'verification-link-sent')
    <p class="verify-status">
      認証メールを再送しました。数分お待ちください。
    </p>
  @endif

  <form method="POST" action="{{ route('verification.send') }}">
    @csrf
    <button type="submit" class="verify-main-button">
      認証はこちらから
    </button>
  </form>

  <form method="POST" action="{{ route('verification.send') }}">
    @csrf
    <button type="submit" class="verify-resend-link">
      認証メールを再送する
    </button>
  </form>
</div>
@endsection




