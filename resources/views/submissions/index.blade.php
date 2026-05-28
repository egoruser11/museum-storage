@extends('layouts.app')

@section('title', 'Форма 3 - сдать предмет')

@section('content')
    <div class="page-title">
        <div>
            <h1>Форма 3 · Сдать предмет в музей</h1>
            <p class="muted">Пользователь может предложить предмет в дар или продать его музею.</p>
        </div>
    </div>

    <section class="section">
        <h2>Новая заявка</h2>
        <form method="post" action="{{ route('submissions.store') }}" id="submission-form">
            @csrf
            <div class="grid">
                <div class="field wide">
                    <label for="title">Название предмета</label>
                    <input id="title" name="title" value="{{ old('title') }}" @class(['is-invalid' => $errors->has('title')]) required>
                    @include('partials.field-error', ['name' => 'title'])
                </div>
                <div class="field">
                    <label for="category_id">Категория</label>
                    <select id="category_id" name="category_id" @class(['is-invalid' => $errors->has('category_id')])>
                        <option value="">Не знаю</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) old('category_id') === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @include('partials.field-error', ['name' => 'category_id'])
                </div>
                <div class="field">
                    <label for="desired_action">Тип передачи</label>
                    <select id="desired_action" name="desired_action" @class(['is-invalid' => $errors->has('desired_action')]) required>
                        @foreach ($actions as $value => $label)
                            <option value="{{ $value }}" @selected(old('desired_action') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @include('partials.field-error', ['name' => 'desired_action'])
                </div>
                <div class="field wide">
                    <label for="owner_name">ФИО владельца</label>
                    <input id="owner_name" name="owner_name" value="{{ old('owner_name', auth()->user()->name) }}" @class(['is-invalid' => $errors->has('owner_name')]) required>
                    @include('partials.field-error', ['name' => 'owner_name'])
                </div>
                <div class="field">
                    <label for="contact_email">Email</label>
                    <input id="contact_email" type="email" name="contact_email" value="{{ old('contact_email', auth()->user()->email) }}" @class(['is-invalid' => $errors->has('contact_email')]) required>
                    @include('partials.field-error', ['name' => 'contact_email'])
                </div>
                <div class="field">
                    <label for="contact_phone">Телефон</label>
                    <input id="contact_phone" name="contact_phone" value="{{ old('contact_phone') }}" @class(['is-invalid' => $errors->has('contact_phone')])>
                    @include('partials.field-error', ['name' => 'contact_phone'])
                </div>
                <div class="field" id="desired-price-field">
                    <label for="desired_price">Желаемая цена</label>
                    <input id="desired_price" type="number" step="0.01" min="0" name="desired_price" value="{{ old('desired_price') }}" @class(['is-invalid' => $errors->has('desired_price')])>
                    @include('partials.field-error', ['name' => 'desired_price'])
                </div>
                <div class="field full">
                    <label for="description">Описание</label>
                    <textarea id="description" name="description" @class(['is-invalid' => $errors->has('description')]) required>{{ old('description') }}</textarea>
                    @include('partials.field-error', ['name' => 'description'])
                </div>
                <div class="field full">
                    <label for="provenance">Происхождение</label>
                    <textarea id="provenance" name="provenance" @class(['is-invalid' => $errors->has('provenance')])>{{ old('provenance') }}</textarea>
                    @include('partials.field-error', ['name' => 'provenance'])
                </div>
            </div>
            <div class="actions">
                <button class="button primary" type="submit">Отправить</button>
            </div>
        </form>
    </section>

    <script>
        (() => {
            const action = document.getElementById('desired_action');
            const priceField = document.getElementById('desired-price-field');
            const priceInput = document.getElementById('desired_price');

            if (!action || !priceField || !priceInput) {
                return;
            }

            const syncPrice = () => {
                const isDonation = action.value === 'donate';
                priceField.style.display = isDonation ? 'none' : '';
                priceInput.disabled = isDonation;

                if (isDonation) {
                    priceInput.value = '';
                }
            };

            action.addEventListener('change', syncPrice);
            syncPrice();
        })();
    </script>

    <section class="section">
        <h2>Мои заявки</h2>
        <div class="scroll-table">
            <table>
                <thead>
                    <tr>
                        <th>Дата</th>
                        <th>Предмет</th>
                        <th>Категория</th>
                        <th>Тип</th>
                        <th>Статус</th>
                        <th>Комментарий</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($submissions as $submission)
                        <tr>
                            <td>{{ $submission->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                {{ $submission->title }}
                                @if ($submission->artifact)
                                    <br><a href="{{ route('catalog.show', $submission->artifact) }}">Открыть карточку</a>
                                @endif
                            </td>
                            <td>{{ $submission->category?->name ?? 'Не выбрана' }}</td>
                            <td>{{ $submission->actionLabel() }}</td>
                            <td><span class="badge">{{ $submission->statusLabel() }}</span></td>
                            <td>{{ $submission->admin_note ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">Заявок пока нет.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
