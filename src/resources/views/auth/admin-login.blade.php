@extends('layouts.admin')
@section('title','管理者ログイン')

@section('content')
<div class="center-wrap">
  <div class="auth-card">
    <h1 class="title">管理者ログイン</h1>

    <form method="POST" action="{{ route('login') }}" novalidate>
      @csrf
      <input type="hidden" name="admin_login" value="1">

      <div class="form-group">
        <label for="email">メールアドレス</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}">
        @error('email') <p class="form-error">{{ $message }}</p> @enderror
      </div>

      <div class="form-group">
        <label for="password">パスワード</label>
        <input id="password" type="password" name="password">
        @error('password') <p class="form-error">{{ $message }}</p> @enderror
      </div>

      @if ($errors->has('email') && !$errors->has('password'))
        
      @endif

      <button type="submit" class="btn btn-primary w-100">管理者ログインする</button>
    </form>

   
  </div>
</div>
@endsection

