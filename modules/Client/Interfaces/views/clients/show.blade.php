@extends('layouts.app')

@section('title', 'Клієнт: ' . $client->full_name)

@section('content')
    <div class="card">
        <div class="card-header">
            <h2>Клієнт: {{ $client->full_name }}</h2>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <p><strong>Email:</strong> {{ $client->email }}</p>
                <p><strong>Баланс:</strong> {{ number_format($client->balance, 2) }} {{ $client->currency }}</p>
                <p><strong>Статус:</strong>
                    @if($client->is_active)
                        <span class="badge bg-success">Активний</span>
                    @else
                        <span class="badge bg-danger">Неактивний</span>
                    @endif
                </p>
                <p><strong>Дата реєстрації:</strong> {{ $client->created_at->format('d.m.Y H:i') }}</p>
            </div>

            <h3>Рахунки клієнта</h3>

            @if($client->accounts->isEmpty())
                <p class="text-muted">У клієнта немає рахунків</p>
            @else
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Номер рахунку</th>
                        <th>Баланс</th>
                        <th>Валюта</th>
                        <th>Дата створення</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($client->accounts as $account)
                        <tr>
                            <td>{{ $account->account_number }}</td>
                            <td>{{ number_format($account->balance, 2) }}</td>
                            <td>{{ $account->currency }}</td>
                            <td>{{ $account->created_at->format('d.m.Y H:i') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif

            <a href="{{ route('clients.index') }}" class="btn btn-secondary">← Назад до списку</a>
            <a href="/" class="btn btn-link">На головну</a>
        </div>
    </div>
@endsection
