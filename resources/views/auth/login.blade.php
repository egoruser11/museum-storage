@extends('layouts.app')

@section('title', 'Вход')

@section('content')
    <div class="page-title">
        <div>
            <h1>Вход</h1>
            <p class="muted">Один вход для пользователя и администратора; роль определяется автоматически.</p>
        </div>
    </div>

    <section class="section">
        <form method="post" action="{{ route('login.store') }}">
            @csrf
            <div class="grid">
                <div class="field wide">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" @class(['is-invalid' => $errors->has('email')]) required autofocus>
                    @include('partials.field-error', ['name' => 'email'])
                </div>
                <div class="field wide">
                    <label for="password">Пароль</label>
                    <input id="password" type="password" name="password" @class(['is-invalid' => $errors->has('password')]) required>
                    @include('partials.field-error', ['name' => 'password'])
                </div>
                <div class="field full">
                    <label>
                        <input style="width:auto; min-height:auto;" type="checkbox" name="remember" value="1">
                        Запомнить вход
                    </label>
                </div>
            </div>
            <div class="actions">
                <button class="button primary" type="submit">Войти</button>
                <a class="button" href="{{ route('register') }}">Создать аккаунт</a>
            </div>
        </form>
    </section>
@endsection
