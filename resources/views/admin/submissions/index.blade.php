@extends('layouts.app')

@section('title', 'Админ - заявки на передачу')

@section('content')
    <div class="page-title">
        <div>
            <h1>Заявки на передачу предметов</h1>
            <p class="muted">Администратор принимает предмет в фонд или отклоняет обращение.</p>
        </div>
        <a class="button" href="{{ route('admin.dashboard') }}">Админ-меню</a>
    </div>

    <section class="section">
        <h2>Фильтр</h2>
        <form method="get" action="{{ route('admin.submissions.index') }}">
            <div class="grid">
                <div class="field">
                    <label for="status">Статус</label>
                    <select id="status" name="status">
                        <option value="">Все</option>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="action">Тип передачи</label>
                    <select id="action" name="action">
                        <option value="">Все</option>
                        @foreach ($actions as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['action'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="actions">
                <button class="button primary" type="submit">Применить</button>
                <a class="button" href="{{ route('admin.submissions.index') }}">Сбросить</a>
            </div>
        </form>
    </section>

    <section class="section">
        <h2>Таблица заявок</h2>
        <div class="scroll-table">
            <table>
                <thead>
                    <tr>
                        <th>Дата</th><th>Предмет</th><th>Пользователь</th><th>Категория</th><th>Тип</th><th>Цена</th><th>Статус</th><th>Рассмотрение</th><th>Удаление</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($submissions as $submission)
                        @php($isOwnSubmission = auth()->id() === $submission->user_id)
                        <tr>
                            <td>{{ $submission->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                <strong>{{ $submission->title }}</strong>
                                <br>{{ $submission->description }}
                                @if ($submission->artifact)
                                    <br><a href="{{ route('admin.artifacts.edit', $submission->artifact) }}">Карточка фонда</a>
                                @endif
                            </td>
                            <td>{{ $submission->user->name }}<br>{{ $submission->contact_email }}</td>
                            <td>
                                <form id="submission-{{ $submission->id }}" method="post" action="{{ route('admin.submissions.update', $submission) }}">
                                    @csrf
                                    @method('patch')
                                    <select name="category_id" @class(['is-invalid' => $errors->has('category_id')])>
                                        <option value="">Не выбрана</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" @selected((int) $submission->category_id === $category->id)>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @include('partials.field-error', ['name' => 'category_id'])
                                </form>
                            </td>
                            <td>{{ $submission->actionLabel() }}</td>
                            <td>{{ $submission->desired_price ? number_format((float) $submission->desired_price, 2, ',', ' ').' руб.' : '-' }}</td>
                            <td><span class="badge {{ $submission->status === 'accepted' ? 'sale' : ($submission->status === 'rejected' ? 'danger' : '') }}">{{ $submission->statusLabel() }}</span></td>
                            <td>
                                <div class="grid" style="grid-template-columns: 1fr; gap: 8px;">
                                    @include('partials.field-error', ['name' => 'status'])
                                    <textarea name="admin_note" form="submission-{{ $submission->id }}" placeholder="Комментарий" @class(['is-invalid' => $errors->has('admin_note')])>{{ $submission->admin_note }}</textarea>
                                    @include('partials.field-error', ['name' => 'admin_note'])
                                    @if ($isOwnSubmission)
                                        <span class="badge warn">Подтверждает другой админ</span>
                                    @endif
                                    <div class="table-actions">
                                        <button class="button small" form="submission-{{ $submission->id }}" name="status" value="in_review" type="submit">В работу</button>
                                        <button class="button small primary" form="submission-{{ $submission->id }}" name="status" value="accepted" type="submit" @disabled($isOwnSubmission)>Принять</button>
                                        <button class="button small danger" form="submission-{{ $submission->id }}" name="status" value="rejected" type="submit">Отклонить</button>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <form method="post" action="{{ route('admin.submissions.destroy', $submission) }}" onsubmit="return confirm('Удалить эту заявку на передачу из базы?')">
                                    @csrf
                                    @method('delete')
                                    <button class="button small danger" type="submit">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9">Заявки не найдены.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
