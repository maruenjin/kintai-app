@extends('layouts.guest')
@section('title','管理者ログイン')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ file_exists(public_path('css/admin.css')) ? filemtime(public_path('css/admin.css')) : 1 }}">
<style>
  .auth-card--plain{
    background: transparent;
    border: 0;
    box-shadow: none;
    padding: 0;
    max-width: 560px;
  }
  .auth-card--plain{ border-top: 0; }
  .title{ margin-bottom: 24px; text-align: center; }
  .form-group{ margin: 14px 0 18px; }
  .btn.w-100{ width:100%; }
</style>
@endpush

@section('content')
<div class="page page-white"> 
  <div class="center-wrap" style="min-height: calc(100vh - 64px);">
     <div class="auth-card auth-card--plain"> 
      <h1 class="title">管理者ログイン</h1>

      <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="login-stack"> 
        <div class="form-group">
          <label for="email">メールアドレス</label>
          <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" inputmode="email">
          @error('email') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
          <label for="password">パスワード</label>
          <input id="password" type="password" name="password" autocomplete="current-password">
          @error('password') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100">管理者ログインする</button>
         </div>
      </form>
    </div>
  </div>
</div>
@endsection




