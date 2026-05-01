@extends('shared::layouts.app')

@section('title', 'Дашборд')

@section('content')
    <div class="row">
        <div class="col-12 mb-4">
            <h1>Статистика CRM</h1>
        </div>
    </div>

    <!-- Statistic cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Клієнти</h5>
                    <h2 class="mb-0">{{ $clientsCount }}</h2>
                    <small>всього</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Активні клієнти</h5>
                    <h2 class="mb-0">{{ $activeClientsCount }}</h2>
                    <small>{{ $inactiveClientsCount }} неактивних</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Сума балансів</h5>
                    <h2 class="mb-0">{{ number_format($totalBalance, 2) }}</h2>
                    <small>{{ $totalAccountsBalance }} на рахунках</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">Транзакції</h5>
                    <h2 class="mb-0">{{ $transactionsCount }}</h2>
                    <small>на суму {{ number_format($totalTransactionsAmount, 2) }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Top of clients -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Топ 5 клієнтів за балансом</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                            <th>#</th>
                            <th>Клієнт</th>
                            <th class="text-end">Баланс</th>
                            <th>Валюта</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($topClients as $index => $client)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $client->full_name }}</td>
                                    <td class="text-end">{{ number_format($client->balance, 2) }}</td>
                                    <td>{{ $client->currency }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics of type transactions -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Транзакції за типами</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                            <th>Тип</th>
                            <th class="text-end">Кількість</th>
                            <th class="text-end">Сума</th>
                            <tr>
                            </thead>
                            <tbody>
                            @foreach($amountByType as $type => $data)
                                <tr>
                                    <td>
                                        @switch($type)
                                            @case('deposit') Депозит @break
                                            @case('withdrawal') Зняття @break
                                            @case('transfer_out') Переказ (вихідний) @break
                                            @case('transfer_in') Переказ (вхідний) @break
                                            @case('fee') Комісія @break
                                            @default {{ $type }}
                                        @endswitch
                                    </td>
                                    <td class="text-end">{{ $data['count'] }}</td>
                                    <td class="text-end">{{ number_format($data['total'], 2) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction statuses -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Статуси транзакцій</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                            <th>Статус</th>
                            <th class="text-end">Кількість</th>
                            <th class="text-end">Сума</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($statusStats as $status => $data)
                                <tr>
                                    <td>
                                        @switch($status)
                                            @case('completed') Виконано @break
                                            @case('pending') Очікує @break
                                            @case('failed') Помилка @break
                                            @default {{ $status }}
                                        @endswitch
                                    </td>
                                    <td class="text-end">{{ $data['count'] }}</td>
                                    <td class="text-end">{{ number_format($data['total'], 2) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Last transactions -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Останні 10 транзакцій</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                            <tr>
                                <th>Дата</th>
                                <th>Тип</th>
                                <th class="text-end">Сума</th>
                                <th class="text-center">Статус</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($recentTransactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->created_at->format('d.m.Y H:i') }}</td>
                                    <td>
                                        @switch($transaction->type)
                                            @case('deposit') Депозит @break
                                            @case('withdrawal') Зняття @break
                                            @case('transfer_out') Переказ (вихід) @break
                                            @case('transfer_in') Переказ (вхід) @break
                                            @default {{ $transaction->type }}
                                        @endswitch
                                    </td>
                                    <td class="text-end {{ $transaction->amount > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($transaction->amount, 2) }}
                                    </td>
                                    <td class="text-center">
                                        @if($transaction->status == 'completed')
                                            ✅
                                        @elseif($transaction->status == 'pending')
                                            ⏳
                                        @else
                                            ❌
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
