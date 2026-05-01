@extends('shared::layouts.auth')

@section('title', 'Вхід в систему')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg border-0 rounded-3">
                <div class="card-header bg-primary text-white text-center py-3 rounded-top-3">
                    <h4 class="mb-0">
                        <i class="fas fa-sign-in-alt me-2"></i> Вхід в CRM
                    </h4>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('login.post') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-1"></i> Email
                            </label>
                            <input type="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   id="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   placeholder="example@crm.com"
                                   required autofocus>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-1"></i> Пароль
                            </label>
                            <input type="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   id="password"
                                   name="password"
                                   placeholder="********"
                                   required>
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i> Увійти
                            </button>
                        </div>

                        <div class="text-center mt-3">
                            <a href="{{ route('register') }}" class="text-decoration-none">
                                Немає акаунту? <strong>Зареєструватися</strong>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-3 bg-light">
                <div class="card-body">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i> Тестові облікові записи:<br>
                        <strong>Адмін:</strong> admin@crm.com / password<br>
                        <strong>Користувач:</strong> user@crm.com / password
                    </small>
                </div>
            </div>
        </div>
    </div>
@endsection
