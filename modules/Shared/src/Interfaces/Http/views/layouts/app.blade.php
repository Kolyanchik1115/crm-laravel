<!doctype html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CRM Finance')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">

        <a class="navbar-brand" href="/">CRM Finance</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">

            <ul class="navbar-nav ms-auto align-items-center">

                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/dashboard') }}">Дашборд</a>
                </li>

                @auth
                    @if(!auth()->user()->roles->contains('name', 'USER'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/clients') }}">Клієнти</a>
                        </li>
                    @endif
                @endauth

                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/accounts') }}">Рахунки</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/transactions') }}">Транзакції</a>
                </li>

                @auth
                    <li class="nav-item dropdown ms-3">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2"
                           href="#"
                           role="button"
                           data-bs-toggle="dropdown"
                           aria-expanded="false">

                            <span class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center"
                                  style="width:32px;height:32px;">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                            </span>

                            <!-- Name -->
                            <span>{{ auth()->user()->name ?? 'User' }}</span>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end">

                            <li class="dropdown-item-text">
                                <small class="text-muted">
                                    {{ auth()->user()->email }}
                                </small>
                            </li>

                            <li><hr class="dropdown-divider"></li>

                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item text-danger">
                                        Вийти
                                    </button>
                                </form>
                            </li>

                        </ul>
                    </li>
                @endauth

            </ul>

        </div>
    </div>
</nav>

<!-- Content -->
<main class="py-4">
    <div class="container">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')

    </div>
</main>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
