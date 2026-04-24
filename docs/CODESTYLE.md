# Code Style Guide CRM

## PSR Стандарти

Проект дотримується наступних PSR стандартів:

### PSR-1: Basic Coding Standard

- Файли мають використовувати лише `<?php` теги
- Файли мають використовувати UTF-8 без BOM
- Класи мають бути названі в `StudlyCaps`
- Константи класів мають бути в `UPPER_CASE`
- Методи мають бути названі в `camelCase`

### PSR-4: Autoloading Standard

- Кожен клас знаходиться у власному файлі
- Простір імен (`namespace`) відповідає структурі директорій
- Автозавантаження налаштоване через Composer

### PSR-12: Extended Coding Style

- Розширений стандарт кодування
- Включає всі правила з попередніх PSR
- Визначає правила для структури файлів, відступів, рядків тощо

## Типізація

### Strict Types

```php
<?php

declare(strict_types=1);  // Обов'язково після <?php
```

**Правила:**

- `declare(strict_types=1);` має бути у **кожному** PHP файлі
- Розташовується одразу після відкриваючого `<?php` тегу
- Порожній рядок після декларації

### Type Hints

```php
// ✅ Обов'язкові type hints
use Modules\Shared\src\Domain\ValueObjects\Money;public function executeTransfer(TransferDTO $dto): array
public function findById(int $id): ?Account
private function calculateCommission(Money $amount): float

// ❌ Заборонено
public function executeTransfer($dto)
public function findById($id)
```

**Вимоги:**

- Вказуйте типи для всіх аргументів методів/функцій
- Вказуйте тип повернення (`: type`) для всіх методів
- Використовуйте `?type` для nullable типів
- Використовуйте union types де необхідно: `int|float`

## Іменування

Детальні правила іменування дивіться в **[NAMING_CONVENTIONS.md](NAMING_CONVENTIONS.md)**

**Основні правила:**

- **Класи**: `StudlyCaps` (напр. `TransferService`)
- **Методи**: `camelCase` (напр. `executeTransfer()`)
- **Змінні**: `camelCase` (напр. `$accountBalance`)
- **Константи**: `UPPER_CASE` (напр. `COMMISSION_RATE`)
- **Приватні/захищені властивості**: `$camelCase` (без підкреслень)

## Структура проекту

```
app/
├── DTO/              # Data Transfer Objects
│   └── TransferDTO.php
├── Services/         # Бізнес-логіка
│   └── TransferService.php
├── Repositories/     # Робота з даними
│   └── AccountRepository.php
├── Controllers/      # HTTP контролери
│   └── TransferController.php
├── Models/           # Eloquent моделі
│   └── Account.php
└── Exceptions/       # Кастомні винятки
    └── TransferException.php
```

## Інструменти

### PHP-CS-Fixer

Автоматичне виправлення кодстайлу:

```bash
# Перевірка (dry-run) — покаже що треба виправити
composer cs-check

# Автоматичне виправлення коду
composer cs-fix
```

### PHPStan

Статичний аналіз коду:

```bash
# Запуск аналізу
composer stan
```

## Команди для Docker

Якщо ви використовуєте Docker, запускайте перевірки через контейнер:

```bash
# Перевірка кодстайлу
docker compose exec app composer cs-check

# Виправлення кодстайлу
docker compose exec app composer cs-fix

# Статичний аналіз PHPStan
docker compose exec app composer stan
```

## Чекліст перед комітом

Перед створенням коміту, переконайтеся що:

### ✅ Code Style

- [ ] Запущено `composer cs-check` (або `docker compose exec app composer cs-check`)
- [ ] Немає помилок кодстайлу
- [ ] Всі файли мають `declare(strict_types=1);`
- [ ] Відступи — 4 пробіли, не таби
- [ ] Фігурні дужки на правильних місцях
- [ ] Рядки не довші 120 символів

### ✅ Static Analysis

- [ ] Запущено `composer stan` (або `docker compose exec app composer stan`)
- [ ] Немає помилок PHPStan
- [ ] Всі type hints коректні

### ✅ Автоматичне виправлення

Якщо перевірки не пройшли:

```bash
# Виправте кодстайл автоматично
composer cs-fix

# Додайте виправлені файли
git add .

# Запустіть перевірки знову
composer cs-check && composer stan

# Якщо все добре - робіть коміт
git commit -m "your message"
```

## Швидкі команди

### Локально

```bash
composer cs-check    # перевірка стилю
composer cs-fix      # виправлення стилю
composer stan        # статичний аналіз
```

### Docker

```bash
docker compose exec app composer cs-check
docker compose exec app composer cs-fix
docker compose exec app composer stan
```

## Додаткові ресурси

- [PSR-12: Extended Coding Style](https://www.php-fig.org/psr/psr-12/)
- [PHP-CS-Fixer Documentation](https://github.com/FriendsOfPHP/PHP-CS-Fixer)
- [PHPStan Documentation](https://phpstan.org/)
- [NAMING_CONVENTIONS.md](NAMING_CONVENTIONS.md) — правила іменування
