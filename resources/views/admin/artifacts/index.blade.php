@extends('layouts.app')

@section('title', 'Админ - экспонаты')

@section('content')
    <div class="page-title">
        <div>
            <h1>Экспонаты</h1>
            <p class="muted">CRUD карточек фонда: учет, оценка, статус и доступность для выкупа.</p>
        </div>
        <a class="button" href="{{ route('admin.dashboard') }}">Админ-меню</a>
    </div>

    <section class="section">
        <h2>{{ $editingArtifact ? 'Изменение экспоната' : 'Добавление экспоната' }}</h2>
        <form method="post" action="{{ $editingArtifact ? route('admin.artifacts.update', $editingArtifact) : route('admin.artifacts.store') }}">
            @csrf
            @if ($editingArtifact)
                @method('put')
            @endif
            <div class="grid">
                <div class="field wide">
                    <label for="title">Название</label>
                    <input id="title" name="title" value="{{ old('title', $editingArtifact->title ?? '') }}" @class(['is-invalid' => $errors->has('title')]) required>
                    @include('partials.field-error', ['name' => 'title'])
                </div>
                <div class="field">
                    <label for="inventory_number">Инвентарный номер</label>
                    <input id="inventory_number" value="{{ $editingArtifact?->inventory_number ?? 'Сгенерируется автоматически' }}" readonly>
                </div>
                <div class="field">
                    <label for="category_id">Категория</label>
                    <select id="category_id" name="category_id" @class(['is-invalid' => $errors->has('category_id')]) required>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) old('category_id', $editingArtifact->category_id ?? 0) === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @include('partials.field-error', ['name' => 'category_id'])
                </div>
                <div class="field">
                    <label for="period">Период</label>
                    <input id="period" name="period" value="{{ old('period', $editingArtifact->period ?? '') }}" @class(['is-invalid' => $errors->has('period')])>
                    @include('partials.field-error', ['name' => 'period'])
                </div>
                <div class="field">
                    <label for="material">Материал</label>
                    <input id="material" name="material" value="{{ old('material', $editingArtifact->material ?? '') }}" @class(['is-invalid' => $errors->has('material')])>
                    @include('partials.field-error', ['name' => 'material'])
                </div>
                <div class="field wide">
                    <label for="condition_state">Состояние</label>
                    <input id="condition_state" name="condition_state" value="{{ old('condition_state', $editingArtifact->condition_state ?? '') }}" @class(['is-invalid' => $errors->has('condition_state')]) required>
                    @include('partials.field-error', ['name' => 'condition_state'])
                </div>
                <div class="field">
                    <label for="acquisition_type">Поступление</label>
                    <select id="acquisition_type" name="acquisition_type" @class(['is-invalid' => $errors->has('acquisition_type')]) required>
                        @foreach ($acquisitionTypes as $value => $label)
                            <option value="{{ $value }}" @selected(old('acquisition_type', $editingArtifact->acquisition_type ?? 'storage') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @include('partials.field-error', ['name' => 'acquisition_type'])
                </div>
                <div class="field">
                    <label for="status">Статус</label>
                    <select id="status" name="status" @class(['is-invalid' => $errors->has('status')]) required>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $editingArtifact->status ?? 'in_storage') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @include('partials.field-error', ['name' => 'status'])
                </div>
                <div class="field">
                    <label for="appraised_value">Оценка</label>
                    <input id="appraised_value" type="number" step="0.01" min="0" name="appraised_value" value="{{ old('appraised_value', $editingArtifact->appraised_value ?? 0) }}" @class(['is-invalid' => $errors->has('appraised_value')]) required>
                    @include('partials.field-error', ['name' => 'appraised_value'])
                </div>
                <div class="field">
                    <label for="sale_price">Цена выкупа</label>
                    <input id="sale_price" type="number" step="0.01" min="0" name="sale_price" value="{{ old('sale_price', $editingArtifact->sale_price ?? '') }}" @class(['is-invalid' => $errors->has('sale_price')])>
                    @include('partials.field-error', ['name' => 'sale_price'])
                </div>
                <div class="field full">
                    <label for="description">Описание</label>
                    <textarea id="description" name="description" @class(['is-invalid' => $errors->has('description')])>{{ old('description', $editingArtifact->description ?? '') }}</textarea>
                    @include('partials.field-error', ['name' => 'description'])
                </div>
            </div>
            <div class="actions">
                <button class="button primary" type="submit">{{ $editingArtifact ? 'Сохранить' : 'Добавить' }}</button>
                @if ($editingArtifact)
                    <a class="button" href="{{ route('admin.artifacts.index') }}">Отмена</a>
                @endif
            </div>
        </form>
    </section>

    <section class="section">
        <h2>Фильтр</h2>
        <form method="get" action="{{ route('admin.artifacts.index') }}">
            <div class="grid">
                <div class="field wide">
                    <label for="search">Название, номер или период</label>
                    <input id="search" name="search" value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="field">
                    <label for="filter_category_id">Категория</label>
                    <select id="filter_category_id" name="category_id">
                        <option value="">Все</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) ($filters['category_id'] ?? 0) === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="filter_status">Статус</label>
                    <select id="filter_status" name="status">
                        <option value="">Все</option>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="actions">
                <button class="button primary" type="submit">Применить</button>
                <a class="button" href="{{ route('admin.artifacts.index') }}">Сбросить</a>
            </div>
        </form>
    </section>

    <section class="section">
        <h2>Таблица экспонатов</h2>
        <div class="scroll-table">
            <table>
                <thead>
                    <tr>
                        <th>Номер</th><th>Название</th><th>Категория</th><th>Статус</th><th>Оценка</th><th>Цена</th><th>Заявки</th><th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($artifacts as $artifact)
                        <tr>
                            <td>{{ $artifact->inventory_number }}</td>
                            <td>{{ $artifact->title }}</td>
                            <td>{{ $artifact->category->name }}</td>
                            <td>{{ $artifact->statusLabel() }}</td>
                            <td>{{ number_format((float) $artifact->appraised_value, 2, ',', ' ') }} руб.</td>
                            <td>{{ $artifact->sale_price ? number_format((float) $artifact->sale_price, 2, ',', ' ').' руб.' : '-' }}</td>
                            <td>{{ $artifact->purchase_orders_count }}</td>
                            <td>
                                <div class="table-actions">
                                    <a class="button small" href="{{ route('admin.artifacts.edit', $artifact) }}">Изменить</a>
                                    <form class="inline" method="post" action="{{ route('admin.artifacts.destroy', $artifact) }}" onsubmit="return confirm('Удалить экспонат?')">
                                        @csrf
                                        @method('delete')
                                        <button class="button small danger" type="submit">Удалить</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">Экспонаты не найдены.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
