# Конвенції іменування (Naming Conventions)

## Правила PSR-1

| Елемент       | Стиль              | Приклад                                |
|---------------|--------------------|----------------------------------------|
| **Класи**     | `StudlyCaps`       | `TransferService`, `AccountRepository` |
| **Методи**    | `camelCase`        | `executeTransfer()`, `findById()`      |
| **Змінні**    | `camelCase`        | `$accountFrom`, `$transferAmount`      |
| **Константи** | `UPPER_SNAKE_CASE` | `COMMISSION_RATE`, `STATUS_COMPLETED`  |

## Приклади з CRM

### Класи

```php
// ✅ Добре
class TransferService
class InvoiceService
class AccountRepository

// ❌ Погано
class transfer_service
class invoiceServiceClass
```

### Методи

```php
// ✅ Добре
public function executeTransfer(TransferDTO $dto): array
public function findById(int $id): ?Account

// ❌ Погано
public function do(): array
public function get($id)
```

### Змінні

```php
// ✅ Добре
$accountFrom = $this->accountRepository->findById($fromId);
$transferAmount = $dto->amount->getValue();
$commissionAmount = $this->calculateCommission($dto->amount);

// ❌ Погано
$a = $this->accountRepository->findById($id1);
$amt = $dto->amount->getValue();
$tmp = $this->calculateCommission($dto->amount);
```

### Константи

```php
// ✅ Добре
private const float COMMISSION_RATE = 0.005;
private const float COMMISSION_THRESHOLD = 10000;
private const string STATUS_COMPLETED = 'completed';

// ❌ Погано
private const float commissionRate = 0.005;
private const string status = 'completed';
```

### Параметри методів

```php
// ✅ Добре
public function decrementBalance(int $accountId, string $amount): void
public function createInvoice(CreateInvoiceDTO $dto): Invoice

// ❌ Погано
public function dec(int $id, string $a): void
public function create($data): Invoice
```

## Описові імена замість загальних

| Загальне  | Описове                                  |
|-----------|------------------------------------------|
| `$data`   | `$transferData`, `$invoiceData`          |
| `$result` | `$transferResult`, `$createdInvoice`     |
| `$items`  | `$invoiceItems`, `$lineItems`            |
| `$total`  | `$totalAmount`, `$invoiceTotal`          |
| `$tmp`    | `$commissionAmount`, `$remainingBalance` |

## Висновок

Твій код вже відповідає PSR-1. Дрібні покращення (`$total` → `$invoiceTotalAmount`) зроблять його ще кращим.
