@extends('layouts.app')

@section('title', 'Регистрация')

@section('content')
    <div class="page-title">
        <div>
            <h1>Регистрация пользователя</h1>
            <p class="muted">После регистрации можно отправлять предметы на рассмотрение и оформлять выкуп.</p>
        </div>
    </div>

    <section class="section">
        <form method="post" action="{{ route('register.store') }}">
            @csrf
            <div class="grid">
                <div class="field wide">
                    <label for="name">ФИО</label>
                    <input id="name" name="name" value="{{ old('name') }}" @class(['is-invalid' => $errors->has('name')]) required autofocus>
                    @include('partials.field-error', ['name' => 'name'])
                </div>
                <div class="field wide">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" @class(['is-invalid' => $errors->has('email')]) required>
                    @include('partials.field-error', ['name' => 'email'])
                </div>
                <div class="field wide">
                    <label for="password">Пароль</label>
                    <input id="password" type="password" name="password" @class(['is-invalid' => $errors->has('password')]) required>
                    @include('partials.field-error', ['name' => 'password'])
                </div>
                <div class="field wide">
                    <label for="password_confirmation">Повтор пароля</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" @class(['is-invalid' => $errors->has('password_confirmation')]) required>
                    @include('partials.field-error', ['name' => 'password_confirmation'])
                </div>
            </div>
            <div class="actions">
                <button class="button primary" type="submit">Зарегистрироваться</button>
                <a class="button" href="{{ route('login') }}">Уже есть аккаунт</a>
            </div>
        </form>
    </section>
@endsection
