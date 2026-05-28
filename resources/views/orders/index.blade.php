@extends('layouts.app')

@section('title', 'Форма 4 - мои выкупы')

@section('content')
    <div class="page-title">
        <div>
            <h1>Форма 4 · Мои заявки на выкуп</h1>
            <p class="muted">Заявки создаются из карточки предмета, если он доступен для выкупа.</p>
        </div>
        <a class="button primary" href="{{ route('catalog.index', ['only_sale' => 1]) }}">Найти предмет</a>
    </div>

    <section class="section">
        <h2>Таблица заявок</h2>
        <div class="scroll-table">
            <table>
                <thead>
                    <tr>
                        <th>Дата</th>
                        <th>Предмет</th>
                        <th>Категория</th>
                        <th>Цена</th>
                        <th>Статус</th>
                        <th>Комментарий администратора</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                            <td><a href="{{ route('catalog.show', $order->artifact) }}">{{ $order->artifact->title }}</a></td>
                            <td>{{ $order->artifact->category->name }}</td>
                            <td>{{ number_format((float) $order->offered_price, 2, ',', ' ') }} руб.</td>
                            <td><span class="badge {{ $order->status === 'paid' ? 'sale' : '' }}">{{ $order->statusLabel() }}</span></td>
                            <td>{{ $order->admin_note ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">Заявок на выкуп пока нет.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
