# Карта логування подій

## Правило

> Для кожної нової події одразу визначати **рівень** і **контекст**. Уникати хаотичного логування.

- **info** – нормальний перебіг події (успішне створення, оновлення)
- **warning** – проблема, яка не зупиняє виконання (валідація, не знайдено ресурс)
- **error** – критична помилка (exception, неочікувана поведінка)

**Обов'язкове поле:** `correlation_id` для всіх подій (зв'язує запит з логами).

---

## 1. Transfers

| Подія | Рівень | Контекст |
|-------|--------|----------|
| Переказ створено | `info` | `transfer_id`, `account_from_id`, `account_to_id`, `amount`, `currency`, `correlation_id` |
| Помилка при створенні переказу (InsufficientBalanceException) | `warning` | `account_from_id`, `amount`, `balance`, `commission`, `correlation_id` |
| Помилка при створенні переказу (SameAccountTransferException) | `warning` | `account_from_id`, `account_to_id`, `correlation_id` |
| Помилка валідації при створенні переказу | `warning` | `errors` (масив помилок), `correlation_id` |
| Неочікуваний exception при створенні переказу | `error` | `transfer_id` (якщо є), `error_message`, `trace`, `correlation_id` |

---

## 2. Invoices

| Подія | Рівень | Контекст |
|-------|--------|----------|
| Рахунок-фактуру створено | `info` | `invoice_id`, `client_id`, `total_amount`, `items_count`, `currency`, `status`, `correlation_id` |
| Помилка створення інвойсу (клієнт не знайдений) | `warning` | `client_id`, `correlation_id` |
| Помилка створення інвойсу (послуга не знайдена) | `warning` | `service_id`, `client_id`, `correlation_id` |
| Помилка валідації при створенні інвойсу | `warning` | `errors` (масив помилок), `correlation_id` |
| Неочікуваний exception при створенні інвойсу | `error` | `invoice_id` (якщо є), `client_id`, `error_message`, `trace`, `correlation_id` |

### Приклади коду для InvoiceService

#### Успішне створення інвойсу (`info`)

```php
Log::info('Invoice created successfully', [
    'invoice_id' => $createdInvoice->id,
    'client_id' => $dto->clientId,
    'total_amount' => (float)$createdInvoice->total_amount,
    'items_count' => count($dto->items),
    'currency' => $dto->currency,
    'status' => $createdInvoice->status,
    'correlation_id' => $this->getCorrelationId(),
]);
```

#### Клієнт не знайдений (`warning`)

```php
Log::warning('Invoice creation failed: client not found', [
    'client_id' => $dto->clientId,
    'correlation_id' => $this->getCorrelationId(),
]);
```

#### Послуга не знайдена (`warning`)

```php
Log::warning('Invoice creation failed: service not found', [
    'service_id' => $invoiceItem->serviceId,
    'client_id' => $dto->clientId,
    'correlation_id' => $this->getCorrelationId(),
]);
```

#### Неочікувана помилка (`error`) - обробляється в ExceptionHandler

```php
// В глобальному ExceptionHandler (bootstrap/app.php)
$exceptions->render(function (Throwable $e, Request $request) {
    if ($request->expectsJson() || $request->is('api/*')) {
        Log::error('Unexpected server error', [
            'error_message' => $e->getMessage(),
            'error_code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'correlation_id' => $request->attributes->get('correlation_id'),
        ]);
        return response()->json([
            'message' => 'Внутрішня помилка сервера.',
        ], 500);
    }
});
```

---

## 3. Accounts

| Подія | Рівень | Контекст |
|-------|--------|----------|
| Перегляд балансу рахунку | `info` | `account_id`, `balance`, `currency`, `correlation_id` |
| Оновлення балансу рахунку | `info` | `account_id`, `old_balance`, `new_balance`, `currency`, `correlation_id` |
| Рахунок не знайдено | `warning` | `account_id`, `correlation_id` |
| Неочікуваний exception при роботі з рахунком | `error` | `account_id`, `error_message`, `trace`, `correlation_id` |

---

## Trait для отримання correlation_id

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

## Використання в сервісах

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
        // Через Log::info з correlation_id
        Log::info('Creating invoice', [
            'client_id' => $dto->clientId,
            'correlation_id' => $this->getCorrelationId(),
        ]);
        
        // Або через скорочений метод log()
        $this->log('info', 'Invoice created successfully', [
            'invoice_id' => $invoice->id,
            'client_id' => $dto->clientId,
        ]);
    }
}
```

---

## Приклад повного логування в InvoiceService

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\CreateInvoiceDTO;
use App\Models\Invoice;
use App\Traits\CorrelationIdTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    use CorrelationIdTrait;

    public function createInvoice(CreateInvoiceDTO $dto): Invoice
    {
        $correlationId = $this->getCorrelationId();

        // 1. Перевірка клієнта
        $client = Client::find($dto->clientId);
        if (!$client) {
            Log::warning('Invoice creation failed: client not found', [
                'client_id' => $dto->clientId,
                'correlation_id' => $correlationId,
            ]);
            throw new \DomainException("Client not found");
        }

        // 2. Перевірка послуг
        foreach ($dto->items as $item) {
            $service = Service::find($item->serviceId);
            if (!$service) {
                Log::warning('Invoice creation failed: service not found', [
                    'service_id' => $item->serviceId,
                    'client_id' => $dto->clientId,
                    'correlation_id' => $correlationId,
                ]);
                throw new \DomainException("Service not found");
            }
        }

        // 3. Створення інвойсу
        $invoice = DB::transaction(function () use ($dto) {
            // ... бізнес-логіка
        });

        // 4. Логування успіху
        Log::info('Invoice created successfully', [
            'invoice_id' => $invoice->id,
            'client_id' => $dto->clientId,
            'total_amount' => $invoice->total_amount,
            'items_count' => count($dto->items),
            'currency' => $dto->currency,
            'status' => $invoice->status,
            'correlation_id' => $correlationId,
        ]);

        return $invoice;
    }
}
```

---

## Інвалідація

Логи зберігаються **30 днів**, після чого автоматично видаляються (налаштовується в конфігурації логування).

---

## Налаштування каналів логування

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

### Приклад JSON логу з Docker

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

### Перегляд логів Docker

```bash
# Подивитись останні логи
docker compose logs app --tail=20

# Фільтрація за correlation_id
docker compose logs app 2>&1 | grep "83a74f66"

# Перегляд в реальному часі
docker compose logs app -f
```

### Фільтрація за допомогою jq

```bash
# Всі логи з рівнем ERROR
docker compose logs app 2>&1 | jq 'select(.level_name == "ERROR")'

# Пошук за конкретним контекстом
docker compose logs app 2>&1 | jq 'select(.context.transfer_out_id != null)'
```

### Переваги JSON формату

| Текстовий формат | JSON формат |
|-----------------|-------------|
| `[2026-04-28 12:42:29] local.INFO: Transfer completed` | `{"message":"Transfer completed","level_name":"INFO",...}` |
| Складно парсити | Легко парситься |
| Немає структури | Структуровані поля |
| Погано для агрегації | Ідеально для Fluentd/Filebeat/CloudWatch |
