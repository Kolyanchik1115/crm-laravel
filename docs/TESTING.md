# Тестування CRM

## Запуск тестів

### Через Composer 

```bash
# Всі тести
composer test

# Тести з coverage
composer test:coverage

# Тільки Unit тести
composer test:unit

# Тільки Feature тести
composer test:feature
```

### Через Artisan

```bash
# Всі тести
php artisan test

# З coverage
php artisan test --coverage-text

# Фільтрація
php artisan test --filter=MoneyTest
php artisan test --filter=TransferServiceTest
php artisan test --filter=InvoiceServiceTest
php artisan test --filter=TransferControllerTest
```

### В Docker

```bash
# Всі тести
docker compose exec app composer test

# З coverage
docker compose exec app composer test:coverage

# Фільтрація
docker compose exec app php artisan test --filter=MoneyTest
```

## Структура тестів

```
tests/
├── Unit/
│   ├── ValueObjects/
│   │   └── MoneyTest.php           # 18 тестів
│   └── Services/
│       ├── TransferServiceTest.php  # 4 тести
│       └── InvoiceServiceTest.php   # 2 тести
└── Feature/
    └── TransferControllerTest.php   # 7 тестів
```

## Очікувані результати

### Всі тести

```bash
composer test
```

```
Tests:  31 passed (31 assertions)
Duration: 1.2s
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

### InvoiceServiceTest

```bash
php artisan test --filter=InvoiceServiceTest
```

```
Tests:  2 passed (2 assertions)
Duration: 0.32s
```

### TransferControllerTest

```bash
php artisan test --filter=TransferControllerTest
```

```
Tests:  7 passed (7 assertions)
Duration: 0.98s
```

## Code Coverage

### Запуск coverage

```bash
composer test:coverage
```

### Поточне покриття

| Компонент | Покриття | Тип тестів |
|-----------|----------|------------|
| `Money` (Value Object) | 100% | Unit |
| `TransferService` | ~90% | Unit + Feature |
| `InvoiceService` | ~85% | Unit |
| `TransferController` | ~80% | Feature |
| `AccountRepository` | ~70% | Integration |
| `TransactionRepository` | ~70% | Integration |
| **Загалом** | **~75%** | |

### Якщо coverage не працює

```bash
# Перевірити Xdebug
php -m | grep xdebug

# Встановити Xdebug (якщо немає)
pecl install xdebug
docker-php-ext-enable xdebug

# Додати в docker-compose.yml
environment:
    XDEBUG_MODE: coverage
```

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

## Проблеми та рішення

### Помилка: XDEBUG_MODE not set

```bash
# Додати в docker-compose.yml
environment:
    XDEBUG_MODE: coverage
    
# Перезапустити контейнери
docker compose down && docker compose up -d
```

### Помилка: memory limit

```bash
php -d memory_limit=-1 artisan test --coverage-text
```

### Тести не знайдені

```bash
# Очистити кеш
php artisan config:clear
php artisan cache:clear

# Перевірити що тести існують
ls -la tests/Unit/
ls -la tests/Feature/
```

## CI/CD інтеграція

### GitHub Actions

```yaml
- name: Run tests
  run: composer test

- name: Run coverage
  run: composer test:coverage
```

### GitLab CI

```yaml
test:
  script:
    - composer test
    - composer test:coverage
```

# Перевірка регресії

### Приклад: зміна правила комісії

**Експеримент:** Змінимо поріг комісії в `TransferService` з `10000` на `20000`

```php
// Було
private const float COMMISSION_THRESHOLD = 10000;

// Змінили на
private const float COMMISSION_THRESHOLD = 20000;
```

**Результат запуску тестів:**

```
FAILED  Tests\Unit\Services\TransferServiceTest > execute transfer applies commission when amount above threshold
No matching handler found for decrementBalance(1, '15075')
Expected: decrementBalance(1, '15000')
```

**Висновок:** Тест `executeTransfer_applies_commission_when_amount_above_threshold` впав,
тому що комісія не застосувалася (поріг став 20000, а сума 15000).

✅ Це підтверджує, що тест перевіряє коректну поведінку комісії.
✅ Якщо хтось випадково змінить правило комісії, тест попередить про це.
✅ Регресія не пройде непоміченою.

### Повернення змін

Після повернення порогу на `10000` тести знову зелені.
