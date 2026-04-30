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
## Контекст для transfers та invoices

### Transfers

При неочікуваній помилці в TransferService відправляється:

```php
$scope->setTag('module', 'transfers');
$scope->setTag('action', 'execute');
$scope->setExtra('account_from_id', $accountFromId);
$scope->setExtra('account_to_id', $accountToId);
$scope->setExtra('amount', $amount);
$scope->setExtra('currency', $currency);
$scope->setExtra('transfer_id', $transferId);
$scope->setExtra('correlation_id', $correlationId);
```

### Invoices

При неочікуваній помилці в InvoiceService відправляється:

```php
$scope->setTag('module', 'invoices');
$scope->setTag('action', 'create');
$scope->setExtra('client_id', $clientId);
$scope->setExtra('invoice_id', $invoiceId);
$scope->setExtra('total_amount', $totalAmount);
$scope->setExtra('correlation_id', $correlationId);
```

### Контрольовані помилки (не відправляються в Sentry)

| Помилка | Тип |
|---------|-----|
| Недостатньо коштів | InsufficientBalanceException |
| Однакові рахунки | SameAccountTransferException |
| Клієнт не знайдений | DomainException |
| Послуга не знайдена | DomainException |
| Валідація | ValidationException |
| Ресурс не знайдено | NotFoundHttpException / ModelNotFoundException |

## Release та регресії

### Налаштування release

В `.env`:

```env
SENTRY_RELEASE=1.0.0
```

Або автоматично з git:

```php
'release' => trim(exec('git log --pretty="%h" -n1 HEAD')),
```

### Як інтерпретувати «First seen in release X»

| Release | Що означає |
|---------|-------------|
| `1.0.0` | Помилка з'явилась після деплою версії 1.0.0 |
| `local-dev` | Помилка виникла локально (не впливає на продакшен) |
| `2.0.0` | Регресія - потрібно перевірити зміни між 1.0.0 і 2.0.0 |

### Дії при регресії

1. **Визначити release** в якому вперше з'явилась помилка
2. **Переглянути зміни** між попереднім і цим release
3. **Прийняти рішення:**
    - **Rollback** - якщо критична помилка
    - **Hotfix** - якщо можна швидко виправити
    - **Відкласти** - якщо некритично

### Приклад в Sentry

В деталях події видно:
- **First seen:** after release 1.5.0
- **Last seen:** after release 2.0.0

Це допомагає зрозуміти, що помилка існувала кілька релізів, або з'явилась тільки в новому.

### 5. Перевірка

```bash
# Переконайся що release додано в конфіг
php artisan tinker
>>> config('sentry.release')
>>> exit

# Зроби тестовий запит
curl -X POST http://localhost:8000/api/v1/transfers \
  -H "Content-Type: application/json" \
  -d '{"account_from_id":1,"account_to_id":2,"amount":"777","currency":"UAH"}'
```

### 6. В Sentry дашборді

Після відправки події, перевір що в деталях з'явився `release` (наприклад, `1.0.0` або `local-dev`).


