<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Музейное хранилище')</title>
    <style>
        :root {
            --bg: #f3f6f4;
            --panel: #ffffff;
            --ink: #172033;
            --muted: #637083;
            --line: #d4ddd8;
            --brand: #0d7c73;
            --brand-strong: #075e58;
            --accent: #9f6b2f;
            --danger: #b42318;
            --soft: #eef7f6;
            --soft-warm: #fff8ec;
            --warning: #fff7ed;
        }

        * { box-sizing: border-box; }

        html {
            min-height: 100%;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background:
                linear-gradient(180deg, rgba(13, 124, 115, 0.08), rgba(243, 246, 244, 0) 260px),
                var(--bg);
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 15px;
            letter-spacing: 0;
        }

        a { color: var(--brand-strong); text-decoration: none; }
        a:hover { text-decoration: underline; }

        .topbar {
            background: rgba(255, 255, 255, 0.94);
            border-bottom: 1px solid var(--line);
            box-shadow: 0 10px 30px rgba(23, 32, 51, 0.05);
        }

        .topbar-inner,
        main,
        .footer-inner {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
        }

        .topbar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            min-height: 74px;
        }

        .brand {
            color: var(--ink);
            font-size: 20px;
            font-weight: 700;
            white-space: nowrap;
            letter-spacing: 0;
        }

        .nav {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 8px;
        }

        .nav a,
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--panel);
            color: var(--ink);
            padding: 8px 12px;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 1px 0 rgba(23, 32, 51, 0.03);
        }

        .nav a.active,
        .button.primary {
            background: linear-gradient(135deg, var(--brand), var(--brand-strong));
            border-color: var(--brand);
            color: #ffffff;
        }

        .button.danger {
            color: var(--danger);
            border-color: #f1c3bd;
            background: #fff7f5;
        }

        .button.warning {
            color: #8a531c;
            border-color: #efd6ad;
            background: var(--soft-warm);
        }

        .button:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            box-shadow: none;
        }

        .button.small { min-height: 32px; padding: 6px 10px; font-size: 14px; }

        main {
            flex: 1 0 auto;
            padding: 28px 0 42px;
        }

        .page-title {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 22px;
        }

        h1 { margin: 0; font-size: 28px; line-height: 1.2; }
        h2 { margin: 0 0 14px; font-size: 19px; line-height: 1.25; }
        h3 { margin: 0 0 8px; font-size: 16px; line-height: 1.25; }
        p { line-height: 1.5; }
        .muted { color: var(--muted); }

        .section {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 18px;
            box-shadow: 0 12px 28px rgba(23, 32, 51, 0.04);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 14px;
        }

        .field { grid-column: span 3; display: flex; flex-direction: column; gap: 6px; }
        .field.wide { grid-column: span 6; }
        .field.full { grid-column: 1 / -1; }

        label {
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }

        input,
        select,
        textarea {
            width: 100%;
            min-height: 40px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background-color: #ffffff;
            color: var(--ink);
            padding: 8px 10px;
            font: inherit;
        }

        select {
            appearance: none;
            -webkit-appearance: none;
            min-height: 42px;
            padding-right: 38px;
            background-image:
                linear-gradient(45deg, transparent 50%, var(--muted) 50%),
                linear-gradient(135deg, var(--muted) 50%, transparent 50%);
            background-position:
                calc(100% - 18px) 50%,
                calc(100% - 13px) 50%;
            background-size: 6px 6px, 6px 6px;
            background-repeat: no-repeat;
            box-shadow: inset 0 1px 2px rgba(23, 32, 51, 0.04);
            cursor: pointer;
        }

        select::-ms-expand {
            display: none;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #89bbb3;
            outline: 3px solid rgba(13, 124, 115, 0.14);
            outline-offset: 1px;
        }

        input[readonly],
        input:disabled,
        select:disabled,
        textarea[readonly],
        textarea:disabled {
            background-color: #eef3f1;
            color: var(--muted);
            cursor: not-allowed;
        }

        input.is-invalid,
        select.is-invalid,
        textarea.is-invalid {
            border-color: var(--danger);
            background-color: #fffafa;
        }

        input.is-invalid:focus,
        select.is-invalid:focus,
        textarea.is-invalid:focus {
            outline: 2px solid #ffd0ca;
            outline-offset: 1px;
        }

        textarea { min-height: 96px; resize: vertical; }

        .field-error {
            color: var(--danger);
            font-size: 13px;
            font-weight: 700;
            line-height: 1.35;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            margin-top: 14px;
        }

        table { width: 100%; border-collapse: collapse; }

        th,
        td {
            border-bottom: 1px solid var(--line);
            padding: 11px 10px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #eef4f1;
            color: #27364b;
            font-size: 13px;
        }

        tr:last-child td { border-bottom: 0; }
        .table-actions { display: flex; flex-wrap: wrap; gap: 6px; }
        .inline { display: inline; }

        .flash,
        .errors {
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 18px;
        }

        .flash { background: var(--soft); border: 1px solid #b9dfda; }
        .errors { background: #fff4f2; border: 1px solid #ffd0ca; color: var(--danger); }

        .menu-grid,
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
        }

        .menu-link,
        .stat {
            display: block;
            min-height: 118px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--panel);
            padding: 18px;
            color: var(--ink);
            box-shadow: 0 10px 22px rgba(23, 32, 51, 0.04);
        }

        .menu-link:hover {
            border-color: #a9c8c2;
            text-decoration: none;
        }

        .menu-link strong,
        .stat strong {
            display: block;
            margin-bottom: 8px;
            font-size: 17px;
        }

        .stat span { display: block; font-size: 30px; font-weight: 700; }

        .scroll-table {
            max-height: 460px;
            overflow: auto;
            border: 1px solid var(--line);
            border-radius: 8px;
        }

        .scroll-table table { min-width: 980px; }

        .summary {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 14px;
        }

        .summary span {
            background: #eef2f7;
            border-radius: 999px;
            padding: 8px 12px;
            font-weight: 700;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            background: #eef2f7;
            padding: 5px 9px;
            font-size: 13px;
            font-weight: 700;
        }

        .badge.sale { background: #e8f7ee; color: #166534; }
        .badge.warn { background: var(--warning); color: #9a3412; }
        .badge.danger { background: #fff4f2; color: var(--danger); }
        .badge.blocked { background: #fff4f2; color: var(--danger); }

        .auth-line {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }

        .footer {
            flex-shrink: 0;
            border-top: 1px solid var(--line);
            background: #ffffff;
            color: var(--muted);
            margin-top: 0;
        }

        .footer-inner {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            min-height: 72px;
            font-size: 13px;
        }

        .footer strong {
            color: var(--ink);
        }

        @media (max-width: 900px) {
            .topbar-inner,
            .page-title {
                flex-direction: column;
                align-items: stretch;
            }

            .nav { justify-content: flex-start; }
            .menu-grid,
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .field,
            .field.wide { grid-column: span 6; }
        }

        @media (max-width: 640px) {
            .topbar-inner,
            main,
            .footer-inner { width: min(100% - 20px, 1180px); }

            .menu-grid,
            .stats-grid,
            .grid { grid-template-columns: 1fr; }

            .field,
            .field.wide,
            .field.full { grid-column: 1; }

            .button,
            .nav a { width: 100%; }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="topbar-inner">
            <a class="brand" href="{{ route('home') }}">Музейное хранилище</a>
            <nav class="nav" aria-label="Основная навигация">
                <a class="{{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Меню</a>
                <a class="{{ request()->routeIs('catalog.*') ? 'active' : '' }}" href="{{ route('catalog.index') }}">Каталог</a>
                @auth
                    @if (auth()->user()->isBlocked())
                        <span class="badge blocked">Аккаунт заблокирован</span>
                    @else
                        <a class="{{ request()->routeIs('submissions.*') ? 'active' : '' }}" href="{{ route('submissions.index') }}">Сдать предмет</a>
                        <a class="{{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}">Мои выкупы</a>
                    @endif
                    @if (auth()->user()->isAdmin())
                        <a class="{{ request()->routeIs('admin.*') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Админ</a>
                    @endif
                    <form class="inline" method="post" action="{{ route('logout') }}">
                        @csrf
                        <button class="button small" type="submit">Выйти</button>
                    </form>
                @else
                    <a class="{{ request()->routeIs('login') ? 'active' : '' }}" href="{{ route('login') }}">Вход</a>
                    <a class="{{ request()->routeIs('register') ? 'active' : '' }}" href="{{ route('register') }}">Регистрация</a>
                @endauth
            </nav>
        </div>
    </header>

    <main>
        @if (session('success'))
            <div class="flash">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="errors">
                <strong>Проверьте поля формы.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
    <footer class="footer">
        <div class="footer-inner">
            <span><strong>Музейное хранилище</strong> · учет фонда, передача предметов и выкуп</span>
            <span>Laravel · PostgreSQL · Sanctum</span>
        </div>
    </footer>
</body>
</html>
