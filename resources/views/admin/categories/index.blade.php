@extends('layouts.app')

@section('title', 'Админ - категории')

@section('content')
    <div class="page-title">
        <div>
            <h1>Категории фонда</h1>
            <p class="muted">Справочник используется в карточках экспонатов и заявках пользователей.</p>
        </div>
        <a class="button" href="{{ route('admin.dashboard') }}">Админ-меню</a>
    </div>

    <section class="section">
        <h2>{{ $editingCategory ? 'Изменение категории' : 'Добавление категории' }}</h2>
        <form method="post" action="{{ $editingCategory ? route('admin.categories.update', $editingCategory) : route('admin.categories.store') }}">
            @csrf
            @if ($editingCategory)
                @method('put')
            @endif
            <div class="grid">
                <div class="field wide">
                    <label for="name">Название</label>
                    <input id="name" name="name" value="{{ old('name', $editingCategory->name ?? '') }}" @class(['is-invalid' => $errors->has('name')]) required>
                    @include('partials.field-error', ['name' => 'name'])
                </div>
                <div class="field full">
                    <label for="description">Описание</label>
                    <textarea id="description" name="description" @class(['is-invalid' => $errors->has('description')])>{{ old('description', $editingCategory->description ?? '') }}</textarea>
                    @include('partials.field-error', ['name' => 'description'])
                    @include('partials.field-error', ['name' => 'category'])
                </div>
            </div>
            <div class="actions">
                <button class="button primary" type="submit">{{ $editingCategory ? 'Сохранить' : 'Добавить' }}</button>
                @if ($editingCategory)
                    <a class="button" href="{{ route('admin.categories.index') }}">Отмена</a>
                @endif
            </div>
        </form>
    </section>

    <section class="section">
        <h2>Таблица категорий</h2>
        <table>
            <thead>
                <tr><th>Название</th><th>Описание</th><th>Экспонаты</th><th>Заявки</th><th>Действия</th></tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->description ?: '-' }}</td>
                        <td>{{ $category->artifacts_count }}</td>
                        <td>{{ $category->submissions_count }}</td>
                        <td>
                            <div class="table-actions">
                                <a class="button small" href="{{ route('admin.categories.edit', $category) }}">Изменить</a>
                                <form class="inline" method="post" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Удалить категорию?')">
                                    @csrf
                                    @method('delete')
                                    <button class="button small danger" type="submit">Удалить</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">Категории не добавлены.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
