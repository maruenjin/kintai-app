@extends('layouts.guest') 
@section('title','会員登録')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}?v={{ filemtime(public_path('css/auth.css')) }}">
@endpush

@section('content')
<div class="auth-page">

 
  

  <div class="auth-shell">
    <div class="auth-card">

      <h1 class="auth-title">会員登録</h1>

      <form method="POST" action="{{ route('register') }}">
        @csrf

       
        <div class="form-group">
          <label for="name" class="form-label">お名前</label>
          <input id="name" name="name" type="text" class="input-text"
                 value="{{ old('name') }}" autocomplete="name" autofocus>
          @error('name') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        
        <div class="form-group">
          <label for="email" class="form-label">メールアドレス</label>
          <input id="email" name="email" type="email" class="input-text"
                 value="{{ old('email') }}" autocomplete="email">
          @error('email') <div class="form-error">{{ $message }}</div> @enderror
        </div>

       
        <div class="form-group">
          <label for="password" class="form-label">パスワード</label>
          <input id="password" name="password" type="password" class="input-text"
                 autocomplete="new-password">
          @error('password') <div class="form-error">{{ $message }}</div> @enderror
        </div>

       
        <div class="form-group" style="margin-bottom: var(--gap-lg);">
          <label for="password_confirmation" class="form-label">パスワード確認</label>
          <input id="password_confirmation" name="password_confirmation" type="password" class="input-text"
                 autocomplete="new-password">
          @error('password_confirmation') <div class="form-error">{{ $message }}</div> @enderror
        </div>

       
        <button type="submit" class="btn-primary">登録する</button>

       
        <div class="auth-actions">
  <a href="{{ route('login') }}" class="auth-link">ログインはこちら</a>
</div>

      </form>

      
     
    </div>
  </div>
</div>
@endsection


