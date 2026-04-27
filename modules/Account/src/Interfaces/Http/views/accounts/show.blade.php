@extends('shared::layouts.app')

@section('title', 'Рахунок: ' . $account->account_number)

@section('content')
    <div class="card">
        <div class="card-header">
            <h2>Рахунок: {{ $account->account_number }}</h2>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <h5>Інформація про рахунок</h5>
                        <p><strong>ID:</strong> {{ $account->id }}</p>
                        <p><strong>Номер рахунку:</strong> {{ $account->account_number }}</p>
                        <p><strong>Баланс:</strong> {{ number_format($account->balance, 2) }} {{ $account->currency }}
                        </p>
                        <p><strong>Валюта:</strong> {{ $account->currency }}</p>
                        <p><strong>Дата створення:</strong> {{ $account->created_at->format('d.m.Y H:i') }}</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="alert alert-success">
                        <h5>Інформація про клієнта</h5>
                        <p><strong>ПІБ:</strong>
                            <a href="{{ url('/clients/', $account->client->id) }}">
                                {{ $account->client->full_name }}
                            </a>
                        </p>
                        <p><strong>Email:</strong> {{ $account->client->email }}</p>
                        <p><strong>Баланс
                                клієнта:</strong> {{ number_format($account->client->balance, 2) }}
                            {{ $account->client->currency }}
                        </p>
                        <p><strong>Статус:</strong>
                            @if($account->client->is_active)
                                <span class="badge bg-success">Активний</span>
                            @else
                                <span class="badge bg-danger">Неактивний</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ url('/accounts') }}" class="btn btn-secondary">← Назад до списку</a>
                <a href="{{ url('/clients/' . $account->client->id) }}" class="btn btn-info">Переглянути клієнта</a>
                <a href="/" class="btn btn-link">На головну</a>
            </div>
        </div>
    </div>
@endsection
