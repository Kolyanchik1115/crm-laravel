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
