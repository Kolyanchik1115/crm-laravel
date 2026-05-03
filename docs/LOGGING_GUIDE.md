# LOGGING GUIDE CRM

## Зміст

1. [Карта подій](#1-карта-подій)
2. [Correlation ID](#2-correlation-id)
3. [Рівні логування](#3-рівні-логування)
4. [Структуровані логи (JSON)](#4-структуровані-логи-json)
5. [Налаштування каналів](#5-налаштування-каналів)
6. [Логи vs Error Tracking](#6-логи-vs-error-tracking)
7. [Команди для роботи з логами](#7-команди-для-роботи-з-логами)
8. [Інциденти](INCIDENT_RUNBOOK.md)

---

## 1. Карта подій

### Правило

> Для кожної нової події одразу визначати **рівень** і **контекст**. Уникати хаотичного логування.

- **info** – нормальний перебіг події (успішне створення, оновлення)
- **warning** – проблема, яка не зупиняє виконання (валідація, не знайдено ресурс)
- **error** – критична помилка (exception, неочікувана поведінка)

**Обов'язкове поле:** `correlation_id` для всіх подій (зв'язує запит з логами).

### 1.1 Transfers

| Подія | Рівень | Контекст |
|-------|--------|----------|
| Переказ створено | `info` | `transfer_id`, `account_from_id`, `account_to_id`, `amount`, `currency`, `correlation_id` |
| Помилка при створенні переказу (InsufficientBalanceException) | `warning` | `account_from_id`, `amount`, `balance`, `commission`, `correlation_id` |
| Помилка при створенні переказу (SameAccountTransferException) | `warning` | `account_from_id`, `account_to_id`, `correlation_id` |
| Помилка валідації при створенні переказу | `warning` | `errors` (масив помилок), `correlation_id` |
| Неочікуваний exception при створенні переказу | `error` | `transfer_id` (якщо є), `error_message`, `trace`, `correlation_id` |

### 1.2 Invoices

| Подія | Рівень | Контекст |
|-------|--------|----------|
| Рахунок-фактуру створено | `info` | `invoice_id`, `client_id`, `total_amount`, `items_count`, `currency`, `status`, `correlation_id` |
| Помилка створення інвойсу (клієнт не знайдений) | `warning` | `client_id`, `correlation_id` |
| Помилка створення інвойсу (послуга не знайдена) | `warning` | `service_id`, `client_id`, `correlation_id` |
| Помилка валідації при створенні інвойсу | `warning` | `errors` (масив помилок), `correlation_id` |
| Неочікуваний exception при створенні інвойсу | `error` | `invoice_id` (якщо є), `client_id`, `error_message`, `trace`, `correlation_id` |

### 1.3 Accounts

| Подія | Рівень | Контекст |
|-------|--------|----------|
| Перегляд балансу рахунку | `info` | `account_id`, `balance`, `currency`, `correlation_id` |
| Оновлення балансу рахунку | `info` | `account_id`, `old_balance`, `new_balance`, `currency`, `correlation_id` |
| Рахунок не знайдено | `warning` | `account_id`, `correlation_id` |
| Неочікуваний exception при роботі з рахунком | `error` | `account_id`, `error_message`, `trace`, `correlation_id` |

---

## 2. Correlation ID

### Що це?

Унікальний ідентифікатор для кожного HTTP-запиту. Дозволяє зібрати всі логи одного запиту в ланцюжок.

### Як працює?

1. **Middleware** `AddCorrelationId` перехоплює запит
2. Генерує унікальний ID: `Str::uuid()->toString()`
3. Додає в контекст логів: `Log::withContext(['correlation_id' => $id])`
4. Додає заголовок у відповідь: `X-Correlation-Id`

### Trait для отримання correlation_id

```php
<?php

declare(strict_types=1);

namespace App\Traits;

trait CorrelationIdTrait
{
    protected function getCorrelationId(): ?string
    {
        return request()->attributes->get('correlation_id');
    }
    
    protected function log(string $level, string $message, array $context = []): void
    {
        $context['correlation_id'] = $this->getCorrelationId();
        \Illuminate\Support\Facades\Log::$level($message, $context);
    }
}
```

### Використання в сервісах

```php
<?php

namespace App\Services;

use App\Traits\CorrelationIdTrait;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    use CorrelationIdTrait;
    
    public function createInvoice(CreateInvoiceDTO $dto): Invoice
    {
        Log::info('Creating invoice', [
            'client_id' => $dto->clientId,
            'correlation_id' => $this->getCorrelationId(),
        ]);
        
        $this->log('info', 'Invoice created successfully', [
            'invoice_id' => $invoice->id,
            'client_id' => $dto->clientId,
        ]);
    }
}
```

---

## 3. Рівні логування

| Рівень | Коли використовувати | Приклад |
|--------|---------------------|---------|
| `info` | Нормальний перебіг події | Переказ створено, інвойс створено |
| `warning` | Проблема, яка не зупиняє виконання | Недостатньо коштів, клієнт не знайдений |
| `error` | Критична помилка, exception | Помилка БД, неочікуваний exception |

---

## 4. Структуровані логи (JSON)

### Приклад JSON логу

```json
{
    "message": "Transfer completed successfully",
    "context": {
        "correlation_id": "83a74f66-2847-4c87-b20a-651947a70529",
        "transfer_out_id": 30,
        "transfer_in_id": 31,
        "account_from_id": 1,
        "account_to_id": 2,
        "amount": 100.0,
        "currency": "UAH",
        "commission": 0.0
    },
    "level": 200,
    "level_name": "INFO",
    "channel": "local",
    "datetime": "2026-04-28T12:42:29.634992+00:00",
    "extra": {}
}
```

---

## 5. Налаштування каналів

### Конфігурація каналів (`config/logging.php`)

Для структурованого JSON формату додано канал `stderr_json` (для Docker):

```php
'stderr_json' => [
    'driver' => 'monolog',
    'level' => env('LOG_LEVEL', 'debug'),
    'handler' => Monolog\Handler\StreamHandler::class,
    'handler_with' => [
        'stream' => 'php://stderr',
    ],
    'formatter' => Monolog\Formatter\JsonFormatter::class,
    'formatter_with' => [
        'append_newline' => true,
    ],
],
```

### Змінні середовища (`.env`)

```bash
# Для Docker (JSON в stdout)
LOG_CHANNEL=stderr_json

# Рівень логування
LOG_LEVEL=debug
```

### Переваги JSON формату

| Текстовий формат | JSON формат |
|-----------------|-------------|
| Складно парсити | Легко парситься |
| Немає структури | Структуровані поля |
| Погано для агрегації | Ідеально для Fluentd/Filebeat/CloudWatch |

---

## 6. Логи vs Error Tracking

### Правило розділення

> **Sentry (Error Tracking) - для неочікуваних exceptions та критичних помилок, що потребують уваги розробника.**
>
> **Логи - для всіх подій, включаючи контрольовані бізнес-відмови.**

| Тип події | Логувати (Log) | Відправляти в Sentry |
|-----------|----------------|----------------------|
| Переказ успішно | ✅ `info` | ❌ |
| InsufficientBalanceException | ✅ `warning` | ❌ (очікувана бізнес-помилка) |
| SameAccountTransferException | ✅ `warning` | ❌ |
| DomainException | ✅ `warning` | ❌ |
| Помилка валідації (422) | ❌ | ❌ |
| Неочікуваний exception | ✅ `error` | ✅ |
| Помилка БД | ✅ `error` | ✅ |
| ConnectionException | ✅ `error` | ✅ |
| ModelNotFoundException | ✅ `warning` | ❌ |

### Які exceptions НЕ відправляти в Sentry

```php
$exceptions->dontReport([
    InsufficientBalanceException::class,
    SameAccountTransferException::class,
    ValidationException::class,
    ModelNotFoundException::class,
    \DomainException::class,
]);
```

---

## 7. Команди для роботи з логами

### Локальні логи

```bash
# Перегляд в реальному часі
tail -f storage/logs/laravel.log

# Пошук за correlation_id
grep "83a74f66" storage/logs/laravel.log

# Фільтр за рівнем
grep "ERROR" storage/logs/laravel.log
```

### Docker логи

```bash
# Перегляд останніх логів
docker compose logs app --tail=20

# Перегляд в реальному часі
docker compose logs app -f

# Фільтрація за correlation_id
docker compose logs app 2>&1 | grep "83a74f66"

# Фільтрація за допомогою jq
docker compose logs app 2>&1 | jq 'select(.level_name == "ERROR")'
```
