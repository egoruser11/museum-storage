@extends('layouts.app')

@section('title', 'Админ - заявки на выкуп')

@section('content')
    <div class="page-title">
        <div>
            <h1>Заявки на выкуп</h1>
            <p class="muted">После статуса «Оплачена» предмет автоматически помечается как выкупленный.</p>
        </div>
        <a class="button" href="{{ route('admin.dashboard') }}">Админ-меню</a>
    </div>

    <section class="section">
        <h2>Фильтр</h2>
        <form method="get" action="{{ route('admin.orders.index') }}">
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
            </div>
            <div class="actions">
                <button class="button primary" type="submit">Применить</button>
                <a class="button" href="{{ route('admin.orders.index') }}">Сбросить</a>
            </div>
        </form>
    </section>

    <section class="section">
        <h2>Таблица заявок</h2>
        <div class="scroll-table">
            <table>
                <thead>
                    <tr><th>Дата</th><th>Предмет</th><th>Покупатель</th><th>Цена</th><th>Статус</th><th>Рассмотрение</th><th>Удаление</th></tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        @php($isOwnOrder = auth()->id() === $order->user_id)
                        <tr>
                            <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                            <td>{{ $order->artifact->title }}<br><span class="muted">{{ $order->artifact->inventory_number }}</span></td>
                            <td>{{ $order->buyer_name }}<br>{{ $order->buyer_email }}</td>
                            <td>{{ number_format((float) $order->offered_price, 2, ',', ' ') }} руб.</td>
                            <td><span class="badge {{ in_array($order->status, ['approved', 'paid'], true) ? 'sale' : ($order->status === 'rejected' ? 'danger' : '') }}">{{ $order->statusLabel() }}</span></td>
                            <td>
                                <form method="post" action="{{ route('admin.orders.update', $order) }}">
                                    @csrf
                                    @method('patch')
                                    <div class="grid" style="grid-template-columns: 1fr; gap: 8px;">
                                        @include('partials.field-error', ['name' => 'status'])
                                        <textarea name="admin_note" placeholder="Комментарий" @class(['is-invalid' => $errors->has('admin_note')])>{{ $order->admin_note }}</textarea>
                                        @include('partials.field-error', ['name' => 'admin_note'])
                                        @if ($isOwnOrder)
                                            <span class="badge warn">Подтверждает другой админ</span>
                                        @endif
                                        <div class="table-actions">
                                            <button class="button small primary" name="status" value="approved" type="submit" @disabled($isOwnOrder)>Одобрить</button>
                                            <button class="button small danger" name="status" value="rejected" type="submit">Отклонить</button>
                                            <button class="button small" name="status" value="paid" type="submit" @disabled($isOwnOrder)>Оплачена</button>
                                        </div>
                                    </div>
                                </form>
                            </td>
                            <td>
                                <form method="post" action="{{ route('admin.orders.destroy', $order) }}" onsubmit="return confirm('Удалить эту заявку на выкуп из базы?')">
                                    @csrf
                                    @method('delete')
                                    <button class="button small danger" type="submit">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">Заявки не найдены.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
