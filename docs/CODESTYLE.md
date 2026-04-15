# Code Style Guide (PSR-12)

## Чекліст перевірки коду

### 1. Strict Types

```php
<?php

declare(strict_types=1);  // ← має бути після <?php
```

**Де перевірити:** всі файли в `app/`

---

### 2. Type Hints

```php
// ✅ Добре
public function executeTransfer(TransferDTO $dto): array
public function findById(int $id): ?Account
private function calculateCommission(Money $amount): float

// ❌ Погано
public function executeTransfer($dto)
public function findById($id)
```
---

### 3. Відступи

- Використовуй **4 пробіли**, не таби
- Без змішування пробілів і табів

```php
// ✅ Добре
public function transfer(Request $request): JsonResponse
{
    $validated = $request->validated();
    return $this->service->execute($validated);
}

// ❌ Погано (таби)
public function transfer(Request $request): JsonResponse
{
	$validated = $request->validated();
	return $this->service->execute($validated);
}
```

---

### 4. Фігурні дужки

- Відкриваюча дужка `{` на тому ж рядку
- Закриваюча дужка `}` на окремому рядку

```php
// ✅ Добре
class TransferService
{
    public function execute(): void
    {
        if ($condition) {
            $this->doSomething();
        }
    }
}

// ❌ Погано
class TransferService {
    public function execute(): void {
        if ($condition) {
            $this->doSomething();
        }
    }
}
```

---

### 5. Пробіли навколо операторів

```php
// ✅ Добре
$result = $a + $b;
$account->balance -= $amount;
$total = $dto->amount->getValue();
function (int $a, int $b) { return $a + $b; }

// ❌ Погано
$result=$a+$b;
$account->balance-=$amount;
function (int $a,int $b){return $a+$b;}
```

---

### 6. Після коми в аргументах

```php
// ✅ Добре
public function transfer(int $fromId, int $toId, float $amount): void
$this->repository->create(['amount' => $amount, 'type' => 'deposit'])

// ❌ Погано
public function transfer(int $fromId,int $toId,float $amount): void
$this->repository->create(['amount' => $amount,'type' => 'deposit'])
```

---

### 7. Довжина рядка

- Максимум **120 символів**
- При перевищенні — перенесення з відступом

```php
// ✅ Добре (перенесення)
$this->accountRepository->decrementBalance(
    $dto->accountFromId,
    (string) $totalDeduct
);

// ❌ Погано (дуже довгий рядок)
$this->accountRepository->decrementBalance($dto->accountFromId, (string) $totalDeduct, $extraParam);
```

---

### 8. Порядок елементів у класі

```php
class TransferService
{
    // 1. Константи
    private const float COMMISSION_RATE = 0.005;
    private const float COMMISSION_THRESHOLD = 10000;

    // 2. Властивості (public, protected, private)
    protected AccountRepositoryInterface $accountRepository;
    protected TransactionRepositoryInterface $transactionRepository;

    // 3. Конструктор
    public function __construct(
        protected AccountRepositoryInterface $accountRepository,
        protected TransactionRepositoryInterface $transactionRepository,
    ) {}

    // 4. Публічні методи
    public function executeTransfer(TransferDTO $dto): array {}

    // 5. Захищені методи
    protected function validate(): void {}

    // 6. Приватні методи
    private function calculateCommission(Money $amount): float {}
}
```

## Команди для автоматичної перевірки

```bash
# Перевірка синтаксису всіх PHP файлів
find app -name "*.php" -exec php -l {} \;

# Перевірка наявності declare(strict_types=1)
grep -r "declare(strict_types=1)" app/ --include="*.php" | wc -l

# Пошук файлів без strict_types
find app -name "*.php" -exec sh -c 'grep -q "declare(strict_types=1)" "$1" || echo "$1"' _ {} \;
```

