# Підтримка API

## Що оновлювати при зміні API

| Зміна | Що оновлювати |
|-------|---------------|
| Додавання ендпоінта | OpenAPI, Feature-тест, API_CONTRACT.md |
| Зміна формату відповіді | Resource, OpenAPI схема, тести (assertJsonStructure) |
| Зміна статусу відповіді | OpenAPI, тести (assertStatus) |
| Зміна формату помилки | Exception Handler, OpenAPI, тести (assertJsonValidationErrors) |
| Зміна валідації | Request, OpenAPI, тести |

---

## Порядок дій

### 1. Додавання нового ендпоінта

1. Реалізуй ендпоінт (Controller, Service, Repository)
2. Опиши в OpenAPI (path, responses, schemas)
3. Напиши Feature-тест (успішний сценарій + помилки)
4. Онови `API_CONTRACT.md`
5. Запусти тести

### 2. Зміна формату відповіді

1. Онови Resource (`toArray()`)
2. Онови OpenAPI схему
3. Онови тести (`assertJsonStructure`)
4. Запусти тести

### 3. Зміна формату помилки

1. Онови Exception Handler
2. Онови OpenAPI (додай нову схему помилки)
3. Онови тести (`assertJsonPath`, `assertJsonFragment`)
4. Запусти тести

---

## Команди

```bash
# Запуск всіх тестів
docker compose exec app php artisan test

# Запуск конкретного файлу
docker compose exec app php artisan test tests/Feature/Api/TransferApiTest.php

# Запуск конкретного тесту
docker compose exec app php artisan test --filter=test_transfer_index_returns_200_with_list

# Запуск API тестів
docker compose exec app php artisan test --filter=Api

# Запуск Unit тестів
docker compose exec app php artisan test --testsuite=Unit

# Запуск Feature тестів
docker compose exec app php artisan test --testsuite=Feature
```

---

## CI перевірка

Тести запускаються при кожному push. Додай в `.github/workflows/tests.yml`:

```yaml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - run: docker compose exec app php artisan test
```

---

## Просте правило

> **"Змінив API - онови документацію і тести"**

Документація = тести = API. Вони завжди мають збігатися.
---
Перевірка:

```bash
docker compose exec app php artisan test --filter=Api
```
---

## Захист контракту тестами

Тести є захисною сіткою контракту. Якщо змінити:

- **Назву поля** → тест з `assertJsonPath` падає
- **Статус-код** → тест з `assertStatus` падає
- **Структуру відповіді** → тест з `assertJsonStructure` падає

### Приклад

```php
// Тест очікує 422
$response->assertStatus(422);
```

Якщо змінити статус на 400:

```bash
FAILED - Expected 422, got 400
```

### Відкат

Повертаємо 422 → тест зелений ✅

---

## Перевірка

```bash
# Зміни статус з 422 на 400
# Запусти тест
docker compose exec app php artisan test

# Тест впаде ❌

# Відкати зміну
# Запусти тест знову

# Тест зелений ✅
```

---

## Правило

> **Тест падає = контракт порушено**
> **Відкат = контракт відновлено**

Це підтверджує, що тести захищають API контракт.

