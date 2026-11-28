<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-i18n="@yield('title_key')">@yield('title_key')</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>

<body>
    <div class="overlay" id="overlay"></div>
    <aside class="sidebar">
        <div class="sidebar__brand">Hadanti</div>
        <nav class="sidebar__nav">
            <a href="/admin" class="sidebar__link {{ request()->is('admin') ? 'is-active' : '' }}"
                data-i18n="nav.home">الرئيسية</a>
        </nav>
    </aside>

    <div class="main">
        <header class="topbar">
            <button type="button" id="menuBtn" class="menu-btn" aria-label="Menu">☰</button>
            <h1 class="topbar__title" data-i18n="@yield('title_key')"></h1>
            <div class="topbar__user">
                <span id="adminName"></span>
                <button type="button" id="langBtn" class="btn btn--ghost" data-i18n="lang.switch">English</button>
                <button type="button" id="logoutBtn" class="btn btn--ghost" data-i18n="topbar.logout">تسجيل
                    الخروج</button>
            </div>
        </header>

        <main class="content">
            @yield('content')
        </main>
    </div>

    <script src="{{ asset('js/admin/i18n.js') }}"></script>
    <script>
    const body = document.body;
    document.getElementById('menuBtn').addEventListener('click', () => body.classList.toggle('menu-open'));
    document.getElementById('overlay').addEventListener('click', () => body.classList.remove('menu-open'));
    </script>
    @stack('scripts')
</body>

</html>