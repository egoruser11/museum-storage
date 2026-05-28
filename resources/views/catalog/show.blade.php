@extends('layouts.app')

@section('title', $artifact->title)

@section('content')
    <div class="page-title">
        <div>
            <h1>{{ $artifact->title }}</h1>
            <p class="muted">{{ $artifact->inventory_number }} · {{ $artifact->category->name }}</p>
        </div>
        <a class="button" href="{{ route('catalog.index') }}">Назад к каталогу</a>
    </div>

    <section class="section">
        <h2>Карточка предмета</h2>
        <table>
            <tbody>
                <tr><th>Период</th><td>{{ $artifact->period ?: 'Не указан' }}</td></tr>
                <tr><th>Материал</th><td>{{ $artifact->material ?: 'Не указан' }}</td></tr>
                <tr><th>Состояние</th><td>{{ $artifact->condition_state }}</td></tr>
                <tr><th>Поступление</th><td>{{ $artifact->acquisitionLabel() }}</td></tr>
                <tr><th>Статус</th><td>{{ $artifact->statusLabel() }}</td></tr>
                <tr><th>Оценка</th><td>{{ number_format((float) $artifact->appraised_value, 2, ',', ' ') }} руб.</td></tr>
                <tr><th>Описание</th><td>{{ $artifact->description ?: 'Описание не заполнено.' }}</td></tr>
            </tbody>
        </table>
    </section>

    <section class="section">
        <h2>Выкуп предмета</h2>
        @if ($artifact->isAvailableForPurchase())
            <p class="muted">Стоимость выкупа: {{ number_format((float) $artifact->sale_price, 2, ',', ' ') }} руб.</p>
            @auth
                <form method="post" action="{{ route('orders.store', $artifact) }}">
                    @csrf
                    <div class="grid">
                        <div class="field wide">
                            <label for="buyer_name">ФИО покупателя</label>
                            <input id="buyer_name" name="buyer_name" value="{{ old('buyer_name', auth()->user()->name) }}" @class(['is-invalid' => $errors->has('buyer_name')]) required>
                            @include('partials.field-error', ['name' => 'buyer_name'])
                        </div>
                        <div class="field wide">
                            <label for="buyer_email">Email</label>
                            <input id="buyer_email" type="email" name="buyer_email" value="{{ old('buyer_email', auth()->user()->email) }}" @class(['is-invalid' => $errors->has('buyer_email')]) required>
                            @include('partials.field-error', ['name' => 'buyer_email'])
                        </div>
                        <div class="field wide">
                            <label for="buyer_phone">Телефон</label>
                            <input id="buyer_phone" name="buyer_phone" value="{{ old('buyer_phone') }}" @class(['is-invalid' => $errors->has('buyer_phone')])>
                            @include('partials.field-error', ['name' => 'buyer_phone'])
                        </div>
                        <div class="field full">
                            <label for="comment">Комментарий</label>
                            <textarea id="comment" name="comment" @class(['is-invalid' => $errors->has('comment')])>{{ old('comment') }}</textarea>
                            @include('partials.field-error', ['name' => 'comment'])
                            @include('partials.field-error', ['name' => 'artifact'])
                        </div>
                    </div>
                    <div class="actions">
                        <button class="button primary" type="submit">Отправить заявку на выкуп</button>
                    </div>
                </form>
            @else
                <div class="actions">
                    <a class="button primary" href="{{ route('login') }}">Войти для выкупа</a>
                    <a class="button" href="{{ route('register') }}">Зарегистрироваться</a>
                </div>
            @endauth
        @else
            <p class="muted">Этот предмет не выставлен на выкуп.</p>
        @endif
    </section>
@endsection
