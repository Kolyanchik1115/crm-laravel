# Аналіз архітектури переказу коштів

## Поточний код переказу

### Контролер (`TransferController.php`)

```php
public function transfer(TransferRequest $request): JsonResponse
{
    $validated = $request->validated();

    try {
        $result = $this->transferService->executeTransfer(
            fromAccountId: $validated['from_account_id'],
            toAccountId: $validated['to_account_id'],
            amount: $validated['amount'],
            description: $validated['description'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Переказ успішно виконано',
            'data' => $result,
        ]);

    } catch (\Exception $e) {
        Log::error('Transfer failed', [
            'error' => $e->getMessage(),
            'data' => $validated,
        ]);

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 400);
    }
}
```

### Сервіс (`TransferService.php`)

```php
public function executeTransfer(
    int $fromAccountId,
    int $toAccountId,
    float $amount,
    ?string $description = null
): array
{
    $transactionOut = null;
    $transactionIn = null;
    $fromAccount = null;
    $toAccount = null;

    DB::transaction(function () use (
        $fromAccountId, $toAccountId, $amount, $description,
        &$transactionOut, &$transactionIn, &$fromAccount, &$toAccount
    ) {
        $fromAccount = $this->repository->findAccountForUpdate($fromAccountId);
        $toAccount = $this->repository->findAccountForUpdate($toAccountId);

        if (!$fromAccount || !$toAccount) {
            throw new \Exception('Рахунок не знайдено');
        }

        if ($fromAccount->balance < $amount) {
            throw new \Exception('Недостатньо коштів');
        }

        $this->repository->updateAccountBalance($fromAccount, $fromAccount->balance - $amount);
        $this->repository->updateAccountBalance($toAccount, $toAccount->balance + $amount);

        $transactionOut = $this->repository->createTransferOut(
            $fromAccount->id,
            $amount,
            $toAccount->account_number,
            $description
        );

        $transactionIn = $this->repository->createTransferIn(
            $toAccount->id,
            $amount,
            $fromAccount->account_number,
            $description,
            $transactionOut->id
        );
    });

    event(new TransferCompleted(...));

    return [...];
}
```

### Репозиторій (`TransferRepository.php`)

```php
public function findAccountForUpdate(int $accountId): ?Account
{
    return Account::lockForUpdate()->find($accountId);
}

public function updateAccountBalance(Account $account, float $newBalance): bool
{
    $account->balance = $newBalance;
    return $account->save();
}

public function createTransferOut(...): Transaction { ... }
public function createTransferIn(...): Transaction { ... }
```

---

## Розподіл відповідальності

### 1. HTTP-шар (Controller)

| Рядок коду | Відповідальність |
|------------|-------------------|
| `$request->validated()` | Отримання валідованих даних |
| `return response()->json(...)` | Формування JSON відповіді |
| `try-catch` | Обробка помилок і статус-кодів |
| `Log::error(...)` | Логування помилок |

**Що тут правильно:**
- ✅ Контролер тонкий (тільки викликає сервіс)
- ✅ Валідація в FormRequest

---

### 2. Бізнес-логіка (Service)

| Рядок коду | Відповідальність |
|------------|-------------------|
| `$fromAccount->balance < $amount` | Перевірка достатності коштів |
| `DB::transaction` | Координація транзакції |
| `event(new TransferCompleted(...))` | Оголошення події |

**Що правильно:**
- ✅ Бізнес-логіка в сервісі
- ✅ Подія після транзакції

---

### 3. Робота з даними (Repository)

| Рядок коду | Відповідальність |
|------------|-------------------|
| `Account::lockForUpdate()->find($id)` | Читання з блокуванням |
| `$account->save()` | Оновлення балансу |
| `Transaction::create(...)` | Створення транзакцій |

**Що правильно:**
- ✅ Запити до БД в репозиторії
- ✅ `lockForUpdate()` для уникнення гонки

---

## Таблиця відповідальностей

| Що | Де має жити | Поточна реалізація |
|----|-------------|---------------------|
| HTTP-запит, відповідь, статус-коди | **Controller** | ✅ Так |
| Валідація вхідних даних | **FormRequest** | ✅ Так |
| Бізнес-правила (перевірка балансу) | **Service** | ✅ Так |
| Координація транзакції | **Service** | ✅ Так |
| Збереження/читання даних | **Repository** | ✅ Так |
| Події (Event) | **Service** | ✅ Так |
| Логування помилок | **Controller** | ✅ Так |

---

## Пропозиції рефакторингу

### TransferService

```php
// Поточна реалізація вже правильна
// Можна лише додати DTO для параметрів
```

### TransferRepository

```php
// Поточна реалізація правильна
// Можна додати методи для групових операцій
```

### Що можна покращити:

| Зараз | Пропозиція |
|-------|------------|
| `throw new \Exception` | Створити кастомні винятки (`InsufficientFundsException`) |
| `Log::error` в контролері | Винести в Exception Handler |
| Передача окремих параметрів | Використовувати DTO (`TransferData`) |

---

## Висновок

Поточна архітектура переказу коштів **вже відповідає принципам**:
- ✅ Контролер тонкий
- ✅ Бізнес-логіка в сервісі
- ✅ Робота з БД в репозиторії
- ✅ Події для асинхронних реакцій

---

## ✅ Реалізовані покращення (після рефакторингу)

### 1. Додано DTO

```php
// app/DTO/TransferData.php
readonly class TransferData
{
    public function __construct(
        public int $fromAccountId,
        public int $toAccountId,
        public float $amount,
        public ?string $description = null
    ) {}

    public static function fromRequest(TransferRequest $request): self
    {
        return new self(
            fromAccountId: $request->validated('from_account_id'),
            toAccountId: $request->validated('to_account_id'),
            amount: $request->validated('amount'),
            description: $request->validated('description')
        );
    }
}
```

### 2. Створено кастомні винятки

```php
// app/Exceptions/InsufficientFundsException.php
class InsufficientFundsException extends DomainException
{
    public function __construct(float $balance, float $required)
    {
        parent::__construct("Недостатньо коштів: доступно {$balance}, потрібно {$required}");
    }
}

// app/Exceptions/AccountNotFoundException.php
class AccountNotFoundException extends DomainException
{
    public function __construct(int $accountId)
    {
        parent::__construct("Рахунок #{$accountId} не знайдено");
    }
}
```

### 3. Логування винесено в Exception Handler

```php
// app/Exceptions/Handler.php
public function render($request, Throwable $e)
{
    if ($e instanceof InsufficientFundsException) {
        Log::warning('Transfer failed: insufficient funds', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 400);
    }

    if ($e instanceof AccountNotFoundException) {
        Log::warning('Transfer failed: account not found', [
            'message' => $e->getMessage(),
        ]);
        
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 404);
    }

    return parent::render($request, $e);
}
```

### 4. Оновлений сервіс (з DTO та кастомними винятками)

```php
public function executeTransfer(TransferData $data): TransferResultDTO
{
    DB::transaction(function () use ($data, &$result) {
        $fromAccount = $this->repository->findAccountForUpdate($data->fromAccountId);
        $toAccount = $this->repository->findAccountForUpdate($data->toAccountId);

        if (!$fromAccount) {
            throw new AccountNotFoundException($data->fromAccountId);
        }
        
        if (!$toAccount) {
            throw new AccountNotFoundException($data->toAccountId);
        }

        if ($fromAccount->balance < $data->amount) {
            throw new InsufficientFundsException($fromAccount->balance, $data->amount);
        }

        // ... оновлення балансів та створення транзакцій
    });

    event(new TransferCompleted($result));
    
    return $result;
}
```

### 5. Оновлений контролер (без try-catch та Log)

```php
public function transfer(TransferRequest $request): JsonResponse
{
    $transferData = TransferData::fromRequest($request);
    
    $result = $this->transferService->executeTransfer($transferData);

    return response()->json([
        'success' => true,
        'message' => 'Переказ успішно виконано',
        'data' => $result,
    ]);
}
```

---

## Підсумкова таблиця відповідальностей (після рефакторингу)

| Що | Де має жити | Реалізовано |
|----|-------------|-------------|
| HTTP-запит, відповідь, статус-коди | **Controller** | ✅ |
| Валідація вхідних даних | **FormRequest** | ✅ |
| Трансформація даних у DTO | **DTO** | ✅ |
| Бізнес-правила (перевірка балансу) | **Service** | ✅ |
| Координація транзакції | **Service** | ✅ |
| Збереження/читання даних | **Repository** | ✅ |
| Події (Event) | **Service** | ✅ |
| Кастомні винятки | **Exceptions/** | ✅ |
| Логування помилок | **Exception Handler** | ✅ |
| HTTP-статус-коди помилок | **Exception Handler** | ✅ |

---

## Висновок (оновлений)

Після рефакторингу архітектура переказу коштів **повністю відповідає принципам чистої архітектури**:

- ✅ Контролер максимально тонкий (без `try-catch` та `Log`)
- ✅ DTO для типізованої передачі даних
- ✅ Кастомні винятки замість голого `\Exception`
- ✅ Логування та HTTP-статуси винесені в Exception Handler
- ✅ Бізнес-логіка виключно в сервісі
- ✅ Робота з БД виключно в репозиторії
- ✅ Події для асинхронних реакцій
