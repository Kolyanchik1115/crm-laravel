@extends('shared::layouts.app')

@section('title', 'Транзакції')

@section('content')
    <div class="card">
        <div class="card-header">
            <h2>Транзакції</h2>
        </div>
        <div class="card-body">
            @if($transactions->isEmpty())
                <p class="text-muted">Поки що немає жодної транзакції</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-success">
                        <tr>
                            <th>ID</th>
                            <th>Сума</th>
                            <th>Тип</th>
                            <th>Статус</th>
                            <th>Дата</th>
                            <th>Номер рахунку</th>
                            <th>Клієнт</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($transactions as $transaction)
                            <tr>
                                <td class="text-center">{{ $transaction->id }}</td>
                                <td class="text-end {{ $transaction->amount > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($transaction->amount, 2) }}
                                </td>
                                <td class="text-center">
                                    @switch($transaction->type)
                                        @case('deposit')
                                            <span class="badge bg-success">Депозит</span>
                                            @break
                                        @case('withdrawal')
                                            <span class="badge bg-danger">Зняття</span>
                                            @break
                                        @case('transfer')
                                            <span class="badge bg-info">Переказ</span>
                                            @break
                                        @case('payment')
                                            <span class="badge bg-primary">Платіж</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ $transaction->type }}</span>
                                    @endswitch
                                </td>
                                <td class="text-center">
                                    @if($transaction->status == 'completed')
                                        <span class="badge bg-success">Виконано</span>
                                    @elseif($transaction->status == 'pending')
                                        <span class="badge bg-warning">Очікує</span>
                                    @elseif($transaction->status == 'failed')
                                        <span class="badge bg-danger">Помилка</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $transaction->status }}</span>
                                    @endif
                                </td>
                                <td>{{ $transaction->created_at->format('d.m.Y H:i') }}</td>
                                <td class="font-monospace">
                                    <a href="{{ route('accounts.show', $transaction->account->id) }}">
                                        {{ $transaction->account->account_number }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('clients.show', $transaction->account->client->id) }}">
                                        {{ $transaction->account->client->full_name }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
