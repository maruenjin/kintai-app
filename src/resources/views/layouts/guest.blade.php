<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>@yield('title', 'COACHTECH')</title>
  <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
</head>
<body class="bg-gray">
  <header class="site-header is-black">
    <div class="inner">
      <a class="brand" href="{{ url('/') }}">
        <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH" />
      </a>
    </div>
  </header>

  <main class="page page-gray">
    @yield('content')
  </main>
</body>
</html>
