@extends('layouts.app')

@section('title', 'Форма 2 - каталог')

@section('content')
    <div class="page-title">
        <div>
            <h1>Форма 2 · Каталог предметов</h1>
            <p class="muted">Поиск по фонду, фильтр по категории и отдельный режим предметов для выкупа.</p>
        </div>
    </div>

    <section class="section">
        <h2>Поиск</h2>
        <form method="get" action="{{ route('catalog.index') }}">
            <div class="grid">
                <div class="field wide">
                    <label for="search">Название, инвентарный номер, период или материал</label>
                    <input id="search" name="search" value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="field">
                    <label for="category_id">Категория</label>
                    <select id="category_id" name="category_id">
                        <option value="">Все категории</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) ($filters['category_id'] ?? 0) === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="only_sale">Выкуп</label>
                    <select id="only_sale" name="only_sale">
                        <option value="0">Все доступные</option>
                        <option value="1" @selected((bool) ($filters['only_sale'] ?? false))>Только для выкупа</option>
                    </select>
                </div>
            </div>
            <div class="actions">
                <button class="button primary" type="submit">Найти</button>
                <a class="button" href="{{ route('catalog.index') }}">Сбросить</a>
            </div>
        </form>
    </section>

    <section class="section">
        <h2>Таблица экспонатов</h2>
        <div class="summary">
            <span>Записей: {{ $artifacts->count() }}</span>
            <span>Для выкупа: {{ $artifacts->where('status', 'on_sale')->count() }}</span>
        </div>
        <div class="scroll-table">
            <table>
                <thead>
                    <tr>
                        <th>Инв. номер</th>
                        <th>Название</th>
                        <th>Категория</th>
                        <th>Период</th>
                        <th>Состояние</th>
                        <th>Статус</th>
                        <th>Цена</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($artifacts as $artifact)
                        <tr>
                            <td>{{ $artifact->inventory_number }}</td>
                            <td>{{ $artifact->title }}</td>
                            <td>{{ $artifact->category->name }}</td>
                            <td>{{ $artifact->period ?: 'Не указан' }}</td>
                            <td>{{ $artifact->condition_state }}</td>
                            <td>
                                <span class="badge {{ $artifact->status === 'on_sale' ? 'sale' : ($artifact->status === 'restoration' ? 'warn' : '') }}">
                                    {{ $artifact->statusLabel() }}
                                </span>
                            </td>
                            <td>
                                @if ($artifact->sale_price)
                                    {{ number_format((float) $artifact->sale_price, 2, ',', ' ') }} руб.
                                @else
                                    -
                                @endif
                            </td>
                            <td><a class="button small" href="{{ route('catalog.show', $artifact) }}">Открыть</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">Предметы не найдены.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
