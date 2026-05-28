@extends('layouts.app')

@section('title', 'Админ-панель')

@section('content')
    <div class="page-title">
        <div>
            <h1>Админ-панель</h1>
            <p class="muted">Управление фондом, пользовательскими заявками и выкупом предметов.</p>
        </div>
    </div>

    <section class="stats-grid section">
        <div class="stat"><strong>Категорий</strong><span>{{ $categoriesCount }}</span></div>
        <div class="stat"><strong>Экспонатов</strong><span>{{ $artifactsCount }}</span></div>
        <div class="stat"><strong>На выкуп</strong><span>{{ $saleCount }}</span></div>
        <div class="stat"><strong>Пользователей</strong><span>{{ $usersCount }}</span></div>
    </section>

    <section class="menu-grid">
        <a class="menu-link" href="{{ route('admin.categories.index') }}">
            <strong>Категории</strong>
            Справочник разделов фонда и контроль связей.
        </a>
        <a class="menu-link" href="{{ route('admin.artifacts.index') }}">
            <strong>Экспонаты</strong>
            Карточки предметов, статусы, оценка и цена выкупа.
        </a>
        <a class="menu-link" href="{{ route('admin.submissions.index') }}">
            <strong>Заявки на передачу</strong>
            Рассмотрение предметов от пользователей.
        </a>
        <a class="menu-link" href="{{ route('admin.orders.index') }}">
            <strong>Заявки на выкуп</strong>
            Подтверждение, отклонение и фиксация оплаты.
        </a>
        <a class="menu-link" href="{{ route('admin.users.index') }}">
            <strong>Пользователи</strong>
            Блокировка аккаунтов и контроль активности.
        </a>
        <div class="stat">
            <strong>Новых обращений</strong>
            <span>{{ $newSubmissionsCount + $newOrdersCount }}</span>
        </div>
        <div class="stat">
            <strong>Заблокировано</strong>
            <span>{{ $blockedUsersCount }}</span>
        </div>
    </section>

    <section class="section">
        <h2>Последние заявки на передачу</h2>
        <table>
            <thead>
                <tr><th>Предмет</th><th>Пользователь</th><th>Категория</th><th>Статус</th></tr>
            </thead>
            <tbody>
                @forelse ($latestSubmissions as $submission)
                    <tr>
                        <td>{{ $submission->title }}</td>
                        <td>{{ $submission->user->name }}</td>
                        <td>{{ $submission->category?->name ?? 'Не выбрана' }}</td>
                        <td>{{ $submission->statusLabel() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">Нет заявок.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section class="section">
        <h2>Последние заявки на выкуп</h2>
        <table>
            <thead>
                <tr><th>Предмет</th><th>Пользователь</th><th>Сумма</th><th>Статус</th></tr>
            </thead>
            <tbody>
                @forelse ($latestOrders as $order)
                    <tr>
                        <td>{{ $order->artifact->title }}</td>
                        <td>{{ $order->user->name }}</td>
                        <td>{{ number_format((float) $order->offered_price, 2, ',', ' ') }} руб.</td>
                        <td>{{ $order->statusLabel() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">Нет заявок.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
