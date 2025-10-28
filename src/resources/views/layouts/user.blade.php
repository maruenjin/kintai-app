<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title','COACHTECH')</title>
  <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
  @stack('styles') 

</head>
<body>
<header class="site-header">
  <div class="inner">
    <a class="brand" href="{{ route('user.attendance.index') }}">
      <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH">
    </a>

    <nav class="user-nav">
  <a href="{{ route('user.attendance.index') }}"
     class="{{ request()->routeIs('user.attendance.index') ? 'active' : '' }}">
    勤怠
  </a>

  
  <a href="{{ route('user.attendance.list', ['ym' => now()->format('Y-m')]) }}"
     class="{{ request()->routeIs('user.attendance.list') ? 'active' : '' }}">
    勤怠一覧
  </a>

  
  @php
    $latest = \App\Models\Attendance::ofUser(auth()->id())->latest('work_date')->first();
  @endphp
  @if($latest)
    <a href="{{ route('user.apps.create', ['attendance' => $latest->id]) }}"
       class="{{ request()->routeIs('user.apps.*') ? 'active' : '' }}">
      申請
    </a>
  @else
    <a href="javascript:void(0)" aria-disabled="true" tabindex="-1" class="is-disabled">申請</a>
  @endif
</nav>


    <form method="POST" action="{{ route('logout') }}" class="logout-form">
      @csrf
      <button type="submit" class="btn-ghost">ログアウト</button>
    </form>
  </div>
</header>


<main class="page page-gray page-user">
  @yield('content')
</main>
</body>
</html>

