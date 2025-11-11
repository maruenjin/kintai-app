<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title','COACHTECH')</title>
 <link rel="stylesheet"
      href="{{ asset('css/app.css') }}?v={{ file_exists(public_path('css/app.css')) ? filemtime(public_path('css/app.css')) : 1 }}">
  @stack('styles')
</head>
<body>
<header class="site-header">
  <div class="inner">
    <a class="brand" href="{{ url('/') }}">
      <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH">
    </a>
  </div>
</header>
<main class="page">@yield('content')</main>
</body>
</html>

