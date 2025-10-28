@extends('layouts.guest')
@section('title','会員登録')

@section('content')
  <div class="auth-wrap">
    <h1>会員登録</h1>

    <form method="POST" action="{{ route('register') }}" class="form-stack">
      @csrf

      <label class="form-label">お名前</label>
      <input class="input" type="text" name="name" value="{{ old('name') }}" required>
      @error('name')<p class="form-error">{{ $message }}</p>@enderror

      <label class="form-label">メールアドレス</label>
      <input class="input" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
      @error('email')<p class="form-error">{{ $message }}</p>@enderror

      <label class="form-label">パスワード</label>
      <input class="input" type="password" name="password" required autocomplete="new-password">
      @error('password')<p class="form-error">{{ $message }}</p>@enderror

      <label class="form-label">パスワード確認</label>
      <input class="input" type="password" name="password_confirmation" required autocomplete="new-password">

      <button type="submit" class="btn-black w-full mt-6">登録する</button>
    </form>

    <p class="mt-6 center"><a href="{{ route('login') }}">ログインはこちら</a></p>
  </div>
@endsection

