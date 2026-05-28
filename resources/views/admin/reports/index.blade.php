@extends('layouts.app')

@section('title', 'Админ - отчеты')

@section('content')
    <div class="page-title">
        <div>
            <h1>Отчеты</h1>
            <p class="muted">Итоги по категориям, заявкам пользователей и выкупам.</p>
        </div>
        <a class="button" href="{{ route('admin.dashboard') }}">Админ-меню</a>
    </div>

    <section class="summary">
        <span>Оплаченный выкуп: {{ number_format((float) $revenue, 2, ',', ' ') }} руб.</span>
        <span>Предметов на выкуп: {{ $availableForSale }}</span>
    </section>

    <section class="section">
        <h2>Категории фонда</h2>
        <table>
            <thead>
                <tr><th>Категория</th><th>Экспонатов</th><th>Заявок на передачу</th></tr>
            </thead>
            <tbody>
                @forelse ($categoryRows as $row)
                    <tr>
                        <td>{{ $row->name }}</td>
                        <td>{{ $row->artifacts_count }}</td>
                        <td>{{ $row->submissions_count }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3">Нет данных.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section class="section">
        <h2>Статусы заявок на передачу</h2>
        <table>
            <thead>
                <tr><th>Статус</th><th>Количество</th></tr>
            </thead>
            <tbody>
                @forelse ($submissionRows as $row)
                    <tr>
                        <td>{{ $submissionStatuses[$row->status] ?? $row->status }}</td>
                        <td>{{ $row->total }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2">Нет данных.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section class="section">
        <h2>Статусы заявок на выкуп</h2>
        <table>
            <thead>
                <tr><th>Статус</th><th>Количество</th><th>Сумма</th></tr>
            </thead>
            <tbody>
                @forelse ($orderRows as $row)
                    <tr>
                        <td>{{ $orderStatuses[$row->status] ?? $row->status }}</td>
                        <td>{{ $row->total }}</td>
                        <td>{{ number_format((float) $row->amount, 2, ',', ' ') }} руб.</td>
                    </tr>
                @empty
                    <tr><td colspan="3">Нет данных.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
