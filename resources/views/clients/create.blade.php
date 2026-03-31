@extends('layouts.app')

@section('title', 'Додати клієнта')

@section('content')
    <div class="card">
        <div class="card-header">
            <h2>Додати нового клієнта</h2>
        </div>
        <div class="card-body">
            {{--Added novalidate parametr to check custom excetions--}}
            <form action="{{ route('clients.store') }}" method="POST" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="full_name" class="form-label">ПІБ <span class="text-danger">*</span></label>
                    <input type="text"
                           class="form-control @error('full_name') is-invalid @enderror"
                           id="full_name"
                           name="full_name"
                           value="{{ old('full_name') }}">
                        {{-- required> --}}}
                    @error('full_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email"
                           class="form-control @error('email') is-invalid @enderror"
                           id="email"
                           name="email"
                           value="{{ old('email') }}">
                        {{-- required> --}}}
                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="balance" class="form-label">Баланс</label>
                        <input type="number"
                               step="0.01"
                               class="form-control @error('balance') is-invalid @enderror"
                               id="balance"
                               name="balance"
                               value="{{ old('balance', 0) }}">
                        @error('balance')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="currency" class="form-label">Валюта</label>
                        <select class="form-control @error('currency') is-invalid @enderror" id="currency"
                                name="currency">
                            <option value="UAH" {{ old('currency') == 'UAH' ? 'selected' : '' }}>UAH - Гривня</option>
                            <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD - Долар</option>
                            <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR - Євро</option>
                        </select>
                        @error('currency')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox"
                               class="form-check-input @error('is_active') is-invalid @enderror"
                               id="is_active"
                               name="is_active"
                               value="1"
                            {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Активний клієнт</label>
                        @error('is_active')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('clients.index') }}" class="btn btn-secondary">Скасувати</a>
                    <button type="submit" class="btn btn-success">Зберегти</button>
                </div>
            </form>
        </div>
    </div>
@endsection
