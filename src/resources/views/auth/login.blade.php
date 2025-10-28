@extends('layouts.guest')
@section('title','ログイン')

@section('content')
  <div class="auth-wrap">
    <h1>ログイン</h1>

    <form method="POST" action="{{ route('login') }}" class="form-stack">
      @csrf

      <label class="form-label">メールアドレス</label>
      <input class="input" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
      @error('email')<p class="form-error">{{ $message }}</p>@enderror

      <label class="form-label">パスワード</label>
      <input class="input" type="password" name="password" required autocomplete="current-password">
      @error('password')<p class="form-error">{{ $message }}</p>@enderror

      <button type="submit" class="btn-black w-full mt-6">ログインする</button>
    </form>

    <p class="mt-6 center">
      <a href="{{ route('register') }}">会員登録はこちら</a>
    </p>
  </div>
@endsection


