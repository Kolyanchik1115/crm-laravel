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
