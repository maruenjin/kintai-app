@extends('layouts.guest')
@section('title','ログイン')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}?v={{ filemtime(public_path('css/auth.css')) }}">
@endpush

@section('content')
  <div class="auth-wrap">
    <h1>ログイン</h1>

    <form method="POST" action="{{ route('login') }}" class="form-stack" novalidate>
      @csrf

      <label for="email" class="form-label">メールアドレス</label>
      <input
       id="email"
       class="input"
       type="email"
       name="email"
       value="{{ old('email') }}"
       autocomplete="email"
       @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
>
      @error('email')
     <p id="email-error" class="form-error" role="alert">{{ $message }}</p>
      @enderror

       <label for="password" class="form-label">パスワード</label>
       <input
        id="password"
        class="input"
        type="password"
        name="password"
        autocomplete="current-password"
        @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
>
       @error('password')
      <p id="password-error" class="form-error" role="alert">{{ $message }}</p>
      @enderror
      <button type="submit" class="btn-black w-full mt-6" formnovalidate>ログインする</button>

    </form>

    <p class="mt-6 center">
      <a href="{{ route('register') }}">会員登録はこちら</a>
    </p>
  </div>
@endsection


