@extends('layouts.app')

@section('title', 'Клієнти CRM')

@section('content')
    <div class="card">
        <div class="card-header">
            <h2>Клієнти CRM</h2>
        </div>
        <div class="card-body">
            @if($clients->isEmpty())
                <p class="text-muted">Поки що немає жодного клієнта</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-success">
                        <tr>
                            <th>ПІБ</th>
                            <th>Email</th>
                            <th>Баланс</th>
                            <th>Валюта</th>
                            <th>Кількість рахунків</th>
                            <th>Деталі</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($clients as $client)
                            <tr>
                                <td>{{ $client->full_name }}</td>
                                <td>{{ $client->email }}</td>
                                <td class="text-end">{{ number_format($client->balance, 2) }}</td>
                                <td>{{ $client->currency }}</td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ $client->accounts->count() }}</span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('clients.show', $client->id) }}" class="btn btn-sm btn-success">
                                        Деталі →
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
