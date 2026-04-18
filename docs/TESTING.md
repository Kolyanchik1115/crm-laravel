# Тестування CRM

## Зміст
1. [Що тестувати](#що-тестувати)
2. [Чого не тестувати](#чого-не-тестувати)
3. [Unit vs Integration vs Feature](#unit-vs-integration-vs-feature)
4. [Моки (Mocks)](#моки-mocks)
5. [Структура тесту (Arrange-Act-Assert)](#структура-тесту-arrange-act-assert)
6. [Команди для запуску](#команди-для-запуску)
7. [Docker](#docker)
8. [Code Coverage](#code-coverage)
9. [Чекліст перед комітом](#чекліст-перед-комітом)
10. [Перевірка регресії](#перевірка-регресії)

---

## Що тестувати

| Компонент | Тип тесту | Що перевіряємо |
|-----------|-----------|----------------|
| **Money** (Value Object) | Unit | Валідація суми (>0), валюта (3 символи), методи `isGreaterThan()`, `add()`, `equals()` |
| **TransferService** | Unit + Integration | Перевірка різних рахунків, достатність балансу, комісія (0.5% при сумі > 10000), виклики репозиторіїв |
| **InvoiceService** | Unit | Розрахунок суми, перевірка існування послуг, створення інвойсу |
| **TransferController** | Feature | HTTP статуси, валідація, JSON відповіді, збереження в БД |

---

## Чого не тестувати

| Компонент | Чому не тестуємо | Як тестуємо замість цього |
|-----------|------------------|---------------------------|
| **Контролери як Unit** | Занадто багато залежностей | Feature-тести (через HTTP) |
| **Репозиторії як Unit** | Тільки запити до БД | Integration-тести (з реальною БД) |
| **Міграції** | Тестуються через `RefreshDatabase` | Автоматично при тестах |
| **Конфігурації** | Не містять логіки | Не потребують тестів |

---

## Unit vs Integration vs Feature

| Тип | Що це | Коли використовувати | Приклад |
|-----|-------|---------------------|---------|
| **Unit** | Тест одного класу в ізоляції | Бізнес-логіка, Value Objects | `MoneyTest`, `TransferServiceTest` (з моками) |
| **Integration** | Тест з реальною БД | Репозиторії, взаємодія з БД | `TransferServiceIntegrationTest` |
| **Feature** | Тест через HTTP | API ендпоінти | `TransferControllerTest` |

### Швидкість виконання

| Тип | Швидкість | Кількість |
|-----|-----------|-----------|
| Unit | Дуже швидко (~0.01-0.05с) | 24 тести |
| Integration | Середньо (~0.1-0.2с) | 4 тести |
| Feature | Повільно (~0.5-1с) | 7 тестів |

---

## Моки (Mocks)

### Коли використовувати моки?

| Ситуація | Використовувати моки? |
|----------|----------------------|
| Unit-тест сервісу | ✅ Так (мокаємо репозиторії) |
| Integration-тест | ❌ Ні (використовуємо реальну БД) |
| Feature-тест | ❌ Ні (реальні HTTP запити) |
| Тест Value Object | ❌ Ні (чистий PHP) |

### Приклад мока в Unit-тесті

```php
$this->accountRepository
    ->shouldReceive('findById')
    ->with($fromAccountId)
    ->once()
    ->andReturn($fromAccount);
```

---

## Структура тесту (Arrange-Act-Assert)

```php
#[Test]
public function test_example(): void
{
    // Arrange (підготовка даних)
    $dto = new TransferDTO(...);
    
    // Act (виконання дії)
    $result = $this->transferService->executeTransfer($dto);
    
    // Assert (перевірка результату)
    $this->assertEquals(100, $result['amount']);
}
```

---

## Команди для запуску

### Локально

```bash
# Всі тести
php artisan test

# Всі тести (через composer)
composer test

# Фільтрація
php artisan test --filter=MoneyTest
php artisan test --filter=TransferServiceTest
php artisan test --filter=InvoiceServiceTest
php artisan test --filter=TransferControllerTest

# Тільки Unit тести
composer test:unit

# Тільки Feature тести
composer test:feature

# З coverage (потрібен Xdebug)
php artisan test --coverage-text
composer test:coverage
```

### Docker

```bash
# Всі тести
docker compose exec app composer test

# Фільтрація
docker compose exec app php artisan test --filter=MoneyTest

# Coverage
docker compose exec app composer test:coverage
```

---

## Docker

### Налаштування для coverage

`docker-compose.yml`:
```yaml
environment:
    XDEBUG_MODE: coverage
```

### Перевірка що Xdebug працює

```bash
docker compose exec app php -m | grep xdebug
```

---

## Code Coverage

### Поточне покриття

| Компонент | Покриття | Тип тестів |
|-----------|----------|------------|
| `Money` | 100% | Unit |
| `TransferService` | ~90% | Unit + Integration |
| `InvoiceService` | ~85% | Unit |
| `TransferController` | ~80% | Feature |
| **Загалом** | **~75%** | |

### Запуск coverage

```bash
composer test:coverage
```

---

## Чекліст перед комітом

### Перед тим як зробити commit, переконайся:

- [ ] Всі тести проходять: `composer test`
- [ ] Критична логіка (Money, TransferService, InvoiceService) покрита тестами
- [ ] Нові методи мають тести
- [ ] Немає `dd()` або `dump()` в коді
- [ ] Тести написані за принципом Arrange-Act-Assert
- [ ] Для Unit-тестів використані моки
- [ ] Для Integration-тестів використано `RefreshDatabase`

### Перевірка регресії

- [ ] При зміні логіки відповідний тест падає
- [ ] Після відкату тест знову зелений

---

## Перевірка регресії

### Приклад: зміна правила комісії

**Експеримент:** Змінимо поріг комісії з `10000` на `20000`

```php
// Було
private const float COMMISSION_THRESHOLD = 10000;

// Змінили на
private const float COMMISSION_THRESHOLD = 20000;
```

**Результат:**

```
FAILED  Tests\Unit\Services\TransferServiceTest > execute transfer applies commission when amount above threshold
No matching handler found for decrementBalance(1, '15075')
```

**Висновок:** Тест впав, тому що комісія не застосувалася. ✅ Тест перевіряє коректну поведінку.

---

## Скрипти в composer.json

```json
{
    "scripts": {
        "test:coverage": "php artisan test --coverage-text",
        "test:unit": "php artisan test --testsuite=Unit",
        "test:feature": "php artisan test --testsuite=Feature"
    }
}
```

---

## Результати тестів

### Всі тести

```bash
composer test
```

```
Tests:  37 passed (96 assertions)
Duration: 2.30s
```

### MoneyTest

```bash
php artisan test --filter=MoneyTest
```

```
Tests:  18 passed (26 assertions)
Duration: 0.53s
```

### TransferServiceTest

```bash
php artisan test --filter=TransferServiceTest
```

```
Tests:  4 passed (4 assertions)
Duration: 0.37s
```

### TransferControllerTest

```bash
php artisan test --filter=TransferControllerTest
```

```
Tests:  7 passed (7 assertions)
Duration: 0.98s
```

---

## Висновок

- ✅ Критична логіка покрита тестами
- ✅ Тести падають при зміні бізнес-правил
- ✅ Є Unit, Integration та Feature тести
- ✅ Coverage ~75% (вище мінімального порогу 60%)
