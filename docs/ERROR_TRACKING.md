# ERROR TRACKING WITH SENTRY

## Налаштування

### 1. Встановлення пакету

```bash
composer require sentry/sentry-laravel
php artisan vendor:publish --provider="Sentry\Laravel\ServiceProvider"
```

### 2. Конфігурація DSN

```bash
php artisan sentry:publish --dsn=YOUR_DSN
```

### 3. Змінні середовища (`.env`)

```env
SENTRY_LARAVEL_DSN=https://your-dsn@sentry.io/000000
SENTRY_TRACES_SAMPLE_RATE=0.0
SENTRY_PROFILES_SAMPLE_RATE=0.0
SENTRY_SEND_DEFAULT_PII=false
```

---

## Конфігурація (`config/sentry.php`)

```php
<?php

return [
    'dsn' => env('SENTRY_LARAVEL_DSN'),
    'environment' => env('APP_ENV', 'production'),
    'send_default_pii' => false,
    
    'before_send' => function ($event, $hint) {
        if (app()->environment('local')) {
            return null;
        }
        
        if ($hint && $hint->exception) {
            $ignoreExceptions = [
                Illuminate\Validation\ValidationException::class,
                Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
                App\Exceptions\InsufficientBalanceException::class,
                App\Exceptions\SameAccountTransferException::class,
                Illuminate\Database\Eloquent\ModelNotFoundException::class,
                DomainException::class,
            ];
            
            foreach ($ignoreExceptions as $ignoreClass) {
                if ($hint->exception instanceof $ignoreClass) {
                    return null;
                }
            }
        }
        
        return $event;
    },
];
```

---

## Тестування

### Тестовий маршрут

```php
// routes/api.php
if (app()->environment('local', 'staging')) {
    Route::get('/api/v1/test-sentry', function () {
        throw new \RuntimeException('Test Sentry integration');
    });
}
```

### Перевірка

```bash
curl -X GET http://localhost:8000/api/v1/test-sentry

# Ручне тестування
php artisan tinker
>>> app('sentry')->captureMessage('Manual test');
```

---

## Які помилки відправляються

| Тип помилки | Відправляти |
|-------------|-------------|
| Неочікувані exceptions | ✅ Так |
| Помилки БД (QueryException) | ✅ Так |
| ConnectionException | ✅ Так |
| ValidationException (422) | ❌ Ні |
| NotFoundHttpException (404) | ❌ Ні |
| InsufficientBalanceException | ❌ Ні |
| SameAccountTransferException | ❌ Ні |
| ModelNotFoundException | ❌ Ні |
| DomainException | ❌ Ні |

---

## Sentry Dashboard

- **Environment:** `local`, `staging`, `production` (з APP_ENV)
- **Release:** версія додатку (опційно)
- **Issue:** групування однакових помилок
- **Event Detail:** стек трейс, контекст, теги

---

## Корисні команди

```bash
# Перевірка DSN
php artisan tinker
>>> config('sentry.dsn')

# Відправка тестового повідомлення
>>> app('sentry')->captureMessage('Test message');

# Перевірка останнього event
>>> app('sentry')->getLastEventId()
```
