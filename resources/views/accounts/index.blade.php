@extends('layouts.app')

@section('title', 'Рахунки клієнтів')

@section('content')
    <div class="card">
        <div class="card-header">
            <h2>Рахунки клієнтів</h2>
        </div>
        <div class="card-body">
            @if($accounts->isEmpty())
                <p class="text-muted">Поки що немає жодного рахунку</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-success">
                        <tr>
                            <th>Номер рахунку</th>
                            <th>Баланс</th>
                            <th>Валюта</th>
                            <th>Клієнт</th>
                            <th>Деталі</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($accounts as $account)
                            <tr>
                                <td class="font-monospace">{{ $account->account_number }}</td>
                                <td class="text-end">{{ number_format($account->balance, 2) }}</td>
                                <td class="text-center">{{ $account->currency }}</td>
                                <td>{{ $account->client->full_name }}</td>
                                <td class="text-center">
                                    <a href="{{ route('accounts.show', $account->id) }}" class="btn btn-sm btn-success">
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
