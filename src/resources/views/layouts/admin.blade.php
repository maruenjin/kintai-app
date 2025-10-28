<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title','勤怠管理（管理）')</title>

 
  <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">


 
  @stack('styles')
</head>
<body>

<header class="site-header">
  <div class="inner">
    <a class="brand" href="{{ route('admin.attendance.list') }}">
      <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH">
    </a>

    <nav class="admin-nav">
      <a href="{{ route('admin.attendance.list') }}" class="active">勤怠一覧</a>
      <a href="#">スタッフ一覧</a>
      <a href="#">申請一覧</a>
    </nav>

    <form method="POST" action="{{ route('logout') }}" class="logout-form">
      @csrf
      <button type="submit" class="btn-ghost">ログアウト</button>
    </form>
  </div>
</header>

<main class="page page-gray">
  @yield('content')
</main>

@stack('scripts')
</body>
</html>



