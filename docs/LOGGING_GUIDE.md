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
| Помилка при створенні переказу (InsufficientBalanceException) | `error` | `transfer_id` (якщо є), `account_from_id`, `error_message`, `correlation_id` |
| Помилка при створенні переказу (SameAccountTransferException) | `warning` | `account_from_id`, `account_to_id`, `error_message`, `correlation_id` |
| Помилка валідації при створенні переказу | `warning` | `errors` (масив помилок), `correlation_id` |
| Неочікуваний exception при створенні переказу | `error` | `transfer_id` (якщо є), `error_message`, `trace`, `correlation_id` |

---

## 2. Invoices

| Подія | Рівень | Контекст |
|-------|--------|----------|
| Рахунок-фактуру створено | `info` | `invoice_id`, `client_id`, `total_amount`, `items_count`, `correlation_id` |
| Помилка створення інвойсу (клієнт не знайдений) | `warning` | `client_id`, `correlation_id` |
| Помилка створення інвойсу (послуга не знайдена) | `warning` | `service_id`, `invoice_id` (якщо є), `correlation_id` |
| Помилка валідації при створенні інвойсу | `warning` | `errors` (масив помилок), `correlation_id` |
| Неочікуваний exception при створенні інвойсу | `error` | `invoice_id` (якщо є), `client_id`, `error_message`, `trace`, `correlation_id` |

---

## 3. Accounts

| Подія | Рівень | Контекст |
|-------|--------|----------|
| Перегляд балансу рахунку | `info` | `account_id`, `balance`, `currency`, `correlation_id` |
| Оновлення балансу рахунку | `info` | `account_id`, `old_balance`, `new_balance`, `currency`, `correlation_id` |
| Рахунок не знайдено | `warning` | `account_id`, `correlation_id` |
| Неочікуваний exception при роботі з рахунком | `error` | `account_id`, `error_message`, `trace`, `correlation_id` |

---

## Приклад використання (Laravel)

```php
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

// Генерація correlation_id на початку запиту
$correlationId = Str::uuid()->toString();

// Успішне створення переказу
Log::info('Transfer created successfully', [
    'transfer_id' => $transfer->id,
    'account_from_id' => $transfer->account_from_id,
    'account_to_id' => $transfer->account_to_id,
    'amount' => $transfer->amount,
    'currency' => $transfer->currency,
    'correlation_id' => $correlationId,
]);

// Помилка недостатньо коштів
Log::error('Insufficient balance for transfer', [
    'account_from_id' => $fromAccount->id,
    'error_message' => 'Insufficient balance',
    'correlation_id' => $correlationId,
]);
```

---

## Інвалідація

Логи зберігаються **30 днів**, після чого автоматично видаляються (налаштовується в конфігурації логування).
