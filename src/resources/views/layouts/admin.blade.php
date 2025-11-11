<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>@yield('title','管理画面')</title>

  @php
    $v = fn($rel) => file_exists(public_path($rel)) ? filemtime(public_path($rel)) : null;
  @endphp
  <link rel="stylesheet" href="{{ asset('css/admin.css') }}@if($v('css/admin.css'))?v={{ $v('css/admin.css') }}@endif">
  @stack('styles')
</head>
<body>

 
  <header class="site-header">
    <div class="inner">
      <a class="brand" href="{{ route('admin.attendance.list') }}">
          <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH" class="brand-logo">
       
      </a>

       @auth
      @if(auth()->user()->isAdmin())
        <nav class="admin-nav admin-nav--right">
          <a href="{{ route('admin.attendance.list') }}"
             class="nav-link {{ request()->routeIs('admin.attendance.*') ? 'is-active' : '' }}">勤怠一覧</a>
          <a href="{{ route('admin.staffs.index') }}"
             class="nav-link {{ request()->routeIs('admin.staffs.*') ? 'is-active' : '' }}">スタッフ一覧</a>
          <a href="{{ route('admin.apps.index') }}"
             class="nav-link {{ request()->routeIs('admin.apps.*') ? 'is-active' : '' }}">申請一覧</a>

         
          <form method="POST" action="{{ route('logout') }}" class="nav-logout">
            @csrf
            <button type="submit" class="nav-link">ログアウト</button>
          </form>
        </nav>
      @endif
    @endauth
  </div>
</header>

  
  <main class="page page-gray">
    <div class="admin-container">
      @yield('content')
    </div>
  </main>

 @stack('scripts')
</body>
</html>






