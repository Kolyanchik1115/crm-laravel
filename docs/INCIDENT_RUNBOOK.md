# Сценарій інциденту: «Масово не проходять перекази»

## Опис інциденту

**Симптоми:** Користувачі повідомляють, що перекази не проходять. API повертає 500 або 422 з повідомленням про помилку.

**Час початку:** 14:00

**Вплив:** Користувачі не можуть виконувати фінансові операції.

---

## Послідовність дій

### Крок 1. Перевірка error tracking (Sentry/Bugsnag)

```bash
# Перевірити чи є сплеск issues по transfers
# Які типи винятків?
```

**Перевірити:**
- Чи є нові exception за останні 30 хвилин?
- Які типи помилок домінують?
    - `InsufficientBalanceException` - проблема з балансом
    - `SameAccountTransferException` - однакові рахунки
    - `ConnectionException` - проблеми з БД
    - `TimeoutException` - повільні відповіді

---

### Крок 2. Пошук по логах

```bash
# Фільтр за часом (14:00–14:30)
docker compose logs app --since=2026-04-28T14:00:00 --until=2026-04-28T14:30:00 | grep -E "Transfer"

# Пошук помилок
docker compose logs app 2>&1 | grep -E "ERROR|WARNING" | grep Transfer
```

**Ключові логи:**
- `Transfer failed: insufficient balance`
- `Transfer failed: same account`
- `Transfer failed: account not found`
- `Unexpected error during transfer`

---

### Крок 3. Аналіз по correlation_id

**Отримати correlation_id:**
- З Sentry: в деталях помилки є поле `correlation_id`
- Від користувача: заголовок `X-Correlation-Id` з його запиту

```bash
# Знайти всі логи за correlation_id
docker compose logs app 2>&1 | grep "83a74f66-2847-4c87-b20a-651947a70529"

# Або через jq для JSON логів
docker compose logs app 2>&1 | jq 'select(.context.correlation_id == "83a74f66-...")'
```

---

### Крок 4. Аналіз контексту

**Що перевірити в логах:**

```json
{
    "context": {
        "correlation_id": "...",
        "account_from_id": 1,
        "amount": 500,
        "balance": 100,
        "error_message": "Недостатньо коштів",
        "transfer_out_id": null
    }
}
```

**Питання:**
- `account_from_id` - чи існує рахунок?
- `amount` vs `balance` - чи достатньо коштів?
- `error_message` - яка саме помилка?
- Чи є закономірність (один акаунт, великі суми)?

---

### Крок 5. Тимчасові дії

| Проблема | Тимчасове рішення |
|----------|-------------------|
| БД недоступна | Перезапуск контейнера: `docker compose restart mysql` |
| Повільна БД | Перевірка індексів, EXPLAIN запитів |
| InsufficientBalanceException | Перевірка чи не змінились комісії |
| Зовнішній сервіс недоступний | Ввімкнути fallback або чергу |
| Помилка в коді | Відкат до попередньої версії |

```bash
# Перевірка БД
docker compose exec mysql mysql -u homestead -psecret -e "SHOW PROCESSLIST;"

# Перевірка черг
docker compose exec redis redis-cli LLEN queues:default

# Перезапуск сервісів
docker compose restart app
```

---

### Крок 6. Виправлення та post-mortem

**Фікс:**
1. Відтворити проблему локально
2. Написати тест, що падає
3. Виправити код
4. Запушити PR

**Post-mortem (протягом 48 годин):**
- Що сталося?
- Чому це сталося?
- Як виявили?
- Як виправили?
- Що зробити, щоб не повторилось?

---

## Чеклист для команди

При інциденті з переказами:

- [ ] **Sentry/Bugsnag** - перевірити сплеск помилок
- [ ] **Логи** - перевірити за останні 30 хвилин
- [ ] **correlation_id** - взяти з помилки або від користувача
- [ ] **Баланси** - перевірити чи не змінились правила комісій
- [ ] **БД** - перевірити статус, процеси, індекси
- [ ] **Redis** - перевірити черги, кеш
- [ ] **Зовнішні API** - перевірити доступність (якщо є)

---

## Приклад пошуку в логах

### Через Docker logs

```bash
# Всі помилки за останню годину
docker compose logs app --since=1h 2>&1 | grep -E "ERROR|WARNING"

# Фільтр по correlation_id
docker compose logs app 2>&1 | grep "correlation_id.*83a74f66"

# JSON логи через jq
docker compose logs app 2>&1 | jq 'select(.level_name == "ERROR")'
```

### Через лог-агрегатор (наприклад, Loki)

```
{container="app"} |= "Transfer" |= "ERROR" 
| json 
| level_name = "ERROR" 
| context.correlation_id = "83a74f66"
```

---

## Роль correlation_id

| Без correlation_id | З correlation_id |
|-------------------|------------------|
| Розрізнені логи | Всі логи одного запиту |
| Неможливо відтворити сценарій | Легко відтворити |
| Декілька запитів переплутані | Кожен запит унікальний |
| Важко знайти причину | Швидкий аналіз |

**Приклад:** Користувач скаржиться, що переказ не пройшов. Він надає `X-Correlation-Id: 83a74f66-...`. За 5 секунд знаходимо всі логи цього запиту і бачимо причину - недостатньо коштів.


# INCIDENT RUNBOOK

## Сценарій: «Після деплою перекази падають»

### Опис інциденту

**Симптоми:** Після деплою release `2.1.0` користувачі почали повідомляти про помилки при створенні переказів. API повертає 500.

**Алерт з Sentry:** Спалеск нового issue `PHP-LARAVEL-7` з рівнем `Error`.

---

### Послідовність дій команди

#### Крок 1. Аналіз в Sentry

Відкрити issue в Sentry та перевірити:

```markdown
| Поле | Що дивитись |
|------|-------------|
| **Frequency** | Скільки разів помилка сталася за останню годину |
| **Affected Users** | Скільки користувачів постраждало |
| **Release** | `2.1.0` - чи це новий реліз? |
| **First seen** | Чи збігається з часом деплою? |
```

**Приклад issue в Sentry:**

```json
{
    "issue_id": "PHP-LARAVEL-7",
    "title": "RuntimeException in TransferService",
    "level": "error",
    "release": "2.1.0",
    "events": 47,
    "users": 12,
    "first_seen": "2026-04-30T13:25:41Z",
    "tags": {
        "module": "transfers",
        "action": "execute",
        "correlation_id": "cc23770d-7cf3-4cdd-8687-6f4ba3957140"
    },
    "extra": {
        "account_from_id": 1,
        "account_to_id": 2,
        "amount": "77333333333337.77",
        "currency": "UAH"
    }
}
```

#### Крок 2. Аналіз контексту помилки

Перевірити в Sentry:

- **Tags:** `module=transfers`, `action=execute`, `correlation_id`
- **Extra:** `account_from_id`, `amount`, `currency`
- **Stack trace:** де саме сталася помилка

#### Крок 3. Пошук в логах за correlation_id

```bash
# Взяти correlation_id з Sentry (наприклад: cc23770d-7cf3-4cdd-8687-6f4ba3957140)
docker compose logs app 2>&1 | grep "cc23770d-7cf3-4cdd-8687-6f4ba3957140"

# Або через лог-агрегатор
correlation_id:cc23770d-7cf3-4cdd-8687-6f4ba3957140
```

#### Крок 4. Визначення причини

| Що перевірити | Як |
|---------------|-----|
| **Stack trace** | В Sentry - який файл і рядок |
| **Логи** | Яке повідомлення помилки |
| **Код** | Що змінилось між релізами `2.0.0` і `2.1.0` |

**Можливі причини:**
- Зміна в логіці розрахунку комісії
- Помилка в SQL запиті
- Проблема з підключенням до БД
- Неправильна обробка нового типу даних

#### Крок 5. Прийняття рішення

| Ситуація | Дія |
|----------|-----|
| Критична помилка, багато користувачів | **Rollback** до попереднього релізу |
| Помилка специфічна (один сценарій) | **Hotfix** - розробка фіксу |
| Некритична помилка | Відкласти, додати в беклог |

#### Крок 6. Деплой та перевірка

```bash
# Rollback
git revert release/2.1.0
git push origin main

# Hotfix
git checkout -b hotfix/transfer-issue
# ... фікс коду ...
git commit -m "fix: repair transfer validation"
git push origin hotfix/transfer-issue
# PR → review → merge
```

#### Крок 7. Post-mortem

1. **Що сталося?** - опис проблеми
2. **Чому сталося?** - коренева причина
3. **Як виявили?** - Sentry + logs
4. **Як виправили?** - rollback / hotfix
5. **Як уникнути?** - тести, код-рев'ю, більше логів

---

### Чеклист для on-call

При інциденті після деплою:

- [ ] **Sentry:** перевірити issue (frequency, affected users, release)
- [ ] **Sentry:** перевірити tags (`module`, `action`, `correlation_id`)
- [ ] **Sentry:** перевірити extra (`account_from_id`, `amount`)
- [ ] **Stack trace:** знайти місце помилки
- [ ] **Логи:** пошук за `correlation_id`
- [ ] **Рішення:** rollback або hotfix
- [ ] **Деплой:** виконати та перевірити
- [ ] **Post-mortem:** написати звіт

---

### Зв'язок Sentry ↔ Логи

```
Request → correlation_id: cc23770d-7cf3-4cdd-8687-6f4ba3957140
    ↓
Sentry: issue з correlation_id в tags
    ↓
Лог-агрегація: пошук по correlation_id
    ↓
Всі логи одного запиту (валідація → сервіс → репозиторій → відповідь)
    ↓
Повна картина інциденту
```

---

### Приклад issue в Sentry

```
Issue: PHP-LARAVEL-7
Title: RuntimeException in TransferService
Level: Error
Release: 2.1.0
Events: 47 | Users: 12
First seen: 2026-04-30 13:25:41

Tags:
  module: transfers
  action: execute
  correlation_id: cc23770d-7cf3-4cdd-8687-6f4ba3957140

Extra:
  account_from_id: 1
  account_to_id: 2
  amount: "77333333333337.77"
  currency: "UAH"

Stack trace:
  /app/Services/TransferService.php:36
  /app/Http/Controllers/Api/V1/TransferController.php:49
```

---

### Підсумок

| Крок | Дія |
|------|-----|
| 1 | Sentry: знайти issue, перевірити release |
| 2 | Sentry: перевірити контекст (tags, extra) |
| 3 | Логи: пошук за correlation_id |
| 4 | Причина: stack trace + логи |
| 5 | Рішення: rollback або hotfix |
| 6 | Post-mortem: звіт |

**Головне правило:** Sentry дає проблему, correlation_id зв'язує з логами, разом дають повну картину. 🔗
```
