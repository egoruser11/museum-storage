@extends('layouts.app')

@section('title', 'Форма 1 - главное меню')

@section('content')
    <div class="page-title">
        <div>
            <h1>Форма 1 · Главное меню</h1>
            <p class="muted">Курсовая тема: учет музейного хранилища, прием предметов и заявки на выкуп.</p>
        </div>
        @auth
            <div class="auth-line">Вы вошли как {{ auth()->user()->name }}</div>
        @endauth
    </div>

    <section class="stats-grid section">
        <div class="stat">
            <strong>Категорий</strong>
            <span>{{ $categoriesCount }}</span>
        </div>
        <div class="stat">
            <strong>Экспонатов</strong>
            <span>{{ $artifactsCount }}</span>
        </div>
        <div class="stat">
            <strong>Для выкупа</strong>
            <span>{{ $availableCount }}</span>
        </div>
        <div class="stat">
            <strong>Заявок</strong>
            <span>{{ $submissionsCount }}</span>
        </div>
    </section>

    <section class="menu-grid">
        <a class="menu-link" href="{{ route('catalog.index') }}">
            <strong>Форма 2</strong>
            Каталог музейных предметов с поиском, фильтрами и карточкой экспоната.
        </a>
        <a class="menu-link" href="{{ route('submissions.index') }}">
            <strong>Форма 3</strong>
            Пользовательская заявка: передать предмет в дар или продать музею.
        </a>
        <a class="menu-link" href="{{ route('orders.index') }}">
            <strong>Форма 4</strong>
            Заявки пользователя на выкуп предметов из доступного каталога.
        </a>
        <a class="menu-link" href="{{ route('admin.dashboard') }}">
            <strong>Форма 5</strong>
            Админ-панель: фонд, категории, заявки, выкупы и отчеты.
        </a>
    </section>
@endsection
