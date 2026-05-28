@extends('layouts.app')

@section('title', 'Админ - пользователи')

@section('content')
    <div class="page-title">
        <div>
            <h1>Пользователи</h1>
            <p class="muted">Администратор может заблокировать аккаунт: подача предметов и заявки на выкуп станут недоступны.</p>
        </div>
        <a class="button" href="{{ route('admin.dashboard') }}">Админ-меню</a>
    </div>

    <section class="section">
        <h2>Фильтр</h2>
        <form method="get" action="{{ route('admin.users.index') }}">
            <div class="grid">
                <div class="field wide">
                    <label for="search">ФИО или email</label>
                    <input id="search" name="search" value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="field">
                    <label for="status">Статус</label>
                    <select id="status" name="status">
                        <option value="">Все</option>
                        <option value="active" @selected(($filters['status'] ?? '') === 'active')>Активные</option>
                        <option value="blocked" @selected(($filters['status'] ?? '') === 'blocked')>Заблокированные</option>
                    </select>
                </div>
            </div>
            <div class="actions">
                <button class="button primary" type="submit">Применить</button>
                <a class="button" href="{{ route('admin.users.index') }}">Сбросить</a>
            </div>
        </form>
    </section>

    <section class="section">
        <h2>Таблица пользователей</h2>
        <div class="scroll-table">
            <table>
                <thead>
                    <tr><th>Пользователь</th><th>Роль</th><th>Статус</th><th>Заявки</th><th>Выкупы</th><th>Действия</th></tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->name }}<br><span class="muted">{{ $user->email }}</span></td>
                            <td>{{ $user->isAdmin() ? 'Администратор' : 'Пользователь' }}</td>
                            <td>
                                @if ($user->isBlocked())
                                    <span class="badge blocked">Заблокирован</span>
                                    <br><span class="muted">{{ $user->blocked_at->format('d.m.Y H:i') }}</span>
                                @else
                                    <span class="badge sale">Активен</span>
                                @endif
                            </td>
                            <td>{{ $user->submissions_count }}</td>
                            <td>{{ $user->purchase_orders_count }}</td>
                            <td>
                                @if ($user->isAdmin())
                                    <span class="muted">Админ не блокируется</span>
                                @elseif ($user->isBlocked())
                                    <form method="post" action="{{ route('admin.users.unblock', $user) }}">
                                        @csrf
                                        @method('patch')
                                        <button class="button small primary" type="submit">Разблокировать</button>
                                    </form>
                                @else
                                    <form method="post" action="{{ route('admin.users.block', $user) }}" onsubmit="return confirm('Заблокировать пользователя и запретить ему подачу заявок?')">
                                        @csrf
                                        @method('patch')
                                        <button class="button small warning" type="submit">Заблокировать</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">Пользователи не найдены.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
