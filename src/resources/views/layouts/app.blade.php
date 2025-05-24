<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <title>attendance management app</title>
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    @yield('css')
</head>
<body>
    <header class="header">
        <div class="header__inner">
            <div class="header__title">
                <div class="header__logo">
                    <img src="{{ asset('icon/logo.svg') }}" alt="社名ロゴ" class="header__img">
                </div>

                <nav class="nav">
                    <ul class="nav-form">
                        @auth
                            @if(auth()->check() && auth()->user()->role === 0)
                            <li class="nav__items">
                                <a href="/attendance" class="nav__button">勤怠</a>
                            </li>
                            <li class="nav__items">
                                <a href="/attendance/list" class="nav__button">勤怠一覧</a>
                            </li>
                            <li class="nav__items">
                                <a href="/stamp_correction_request/list" class="nav__button">申請</a>
                            </li>
                            @elseif(auth()->check() && auth()->user()->role === 1)
                            <li class="nav__items">
                                <a href="/admin/attendance/list" class="nav__button">勤怠一覧</a>
                            </li>
                            <li class="nav__items">
                                <a href="/admin/staff/list" class="nav__button">スタッフ一覧</a>
                            </li>
                            <li class="nav__items">
                                <a href="/stamp_correction_request/list" class="nav__button">申請一覧</a>
                            </li>
                            @endif
                            <li class="nav__items">
                                <form action="/logout" method="post">
                                    @csrf
                                    <input type="submit" value="ログアウト" class="nav__button">
                                </form>
                            </li>
                        @endauth
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>
</body>
</html>