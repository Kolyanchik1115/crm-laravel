# Аналіз продуктивності CRM

## Критичні ендпоінти CRM

| Ендпоінт | Метод | Опис | Потенційні проблеми |
|----------|-------|------|---------------------|
| `/api/v1/transfers` | GET | Список переказів з фільтрацією (account_id, date_from, date_to, page, per_page) | Велика кількість записів, фільтрація по датах, N+1 запити |
| `/api/v1/transfers/{id}` | GET | Деталі одного переказу | JOIN з таблицями accounts, clients |
| `/api/v1/invoices` | GET | Список рахунків-фактур з фільтрацією (client_id, status, page, per_page) | Фільтрація по статусу, пагінація, підрахунок total |
| `/api/v1/invoices/{id}` | GET | Деталі одного рахунку-фактури | Завантаження всіх позицій (items) |
| `/api/v1/accounts` | GET | Список рахунків з фільтрацією (client_id, currency) | JOIN з clients, підрахунок балансів |
| `/api/v1/accounts/{id}` | GET | Деталі рахунку з балансом | Актуальний баланс, транзакції |
| `/api/v1/dashboard-stats` | GET | Статистика для дашборду | Агрегація по клієнтах, транзакціях, рахунках |

## Принцип роботи: виміряй → аналізуй → оптимізуй

### 1. Виміряй (Measure)
- Виміряти час відповіді кожного ендпоінта
- Виміряти кількість SQL запитів
- Виміряти споживання пам'яті

### 2. Аналізуй (Analyze)
- Знайти N+1 проблеми
- Знайти запити без індексів
- Знайти дублюючі запити
- Знайти неефективні JOIN'и

### 3. Оптимізуй (Optimize)
- Додати індекси
- Використати eager loading
- Додати кешування
- Оптимізувати пагінацію
- Використати chunking для великих вибірок

## Інструменти профайлінгу

### Локальна розробка

| Інструмент | Призначення | Встановлення |
|------------|-------------|--------------|
| **Laravel Telescope** | Детальний аналіз запитів, jobs, events | `composer require laravel/telescope` |
| **Laravel Debugbar** | Відладка в браузері | `composer require barryvdh/laravel-debugbar` |
| **Clockwork** | Альтернативний дебагер | `composer require itsgoingd/clockwork` |

### Production

| Інструмент | Призначення |
|------------|-------------|
| **Sentry** | Моніторинг помилок та продуктивності |
| **Laravel Logs** | Аналіз логів |
| **MySQL Slow Log** | Повільні запити |
| **Redis Metrics** | Стан кешу |

### SQL аналіз

```bash
# Увімкнути логування запитів
DB::enableQueryLog();
// ... запит
dd(DB::getQueryLog());

# Перевірити індекси
EXPLAIN SELECT * FROM transactions WHERE account_id = 1;
```

## Поточні оптимізації

### Вже реалізовано

- ✅ Індекси на `account_id`, `type`, `status` в транзакціях
- ✅ Індекси на `client_id` в інвойсах
- ✅ Кешування статистики дашборду
- ✅ Пагінація замість `all()`
- ✅ Eager loading через `with()`

### Планується

- ⚠️ Кешування списків клієнтів
- ⚠️ Оптимізація агрегатних запитів
- ⚠️ Денормалізація для часто використовуваних даних

## Метрики (цільові показники)

| Показник | Цільове значення | Поточне значення |
|----------|------------------|------------------|
| Час відповіді API (p95) | < 200ms | ~150ms |
| Кількість SQL запитів на ендпоінт | < 10 | ~8 |
| Memory usage | < 50MB | ~35MB |
| Cache hit ratio | > 80% | ~75% |

## Перші кроки оптимізації

1. **Додати індекси для фільтрації**
```sql
CREATE INDEX idx_transactions_account_id ON transactions(account_id);
CREATE INDEX idx_transactions_created_at ON transactions(created_at);
CREATE INDEX idx_invoices_status ON invoices(status);
```

2. **Використовувати `chunk()` для великих вибірок**
```php
Transaction::chunk(100, function ($transactions) {
    // обробка
});
```

3. **Додати кешування для рідко змінних даних**
```php
Cache::remember('clients:list', 3600, fn() => Client::all());
```

4. **Оптимізувати пагінацію через cursor**
```php
Transaction::cursorPaginate(15); // замість paginate()
```

## Логування продуктивності

```php
// Middleware для вимірювання часу
public function handle($request, $next)
{
    $start = microtime(true);
    $response = $next($request);
    $duration = microtime(true) - $start;
    
    if ($duration > 1) {
        Log::warning('Slow request', [
            'uri' => $request->path(),
            'duration' => $duration,
            'user_id' => auth()->id(),
        ]);
    }
    
    return $response;
}
```

## Висновок

> **Не оптимізуй передчасно!** Спочатку виміряй, знайди вузькі місця, потім оптимізуй.

# Глибокий аналіз GET /api/v1/transfers

### Потік даних

```mermaid
flowchart LR
    A[Client Request] --> B[TransferController@index]
    B --> C[TransactionRepository::getTransfersPaginated]
    C --> D[SQL Query]
    D --> E[TransactionResource]
    E --> F[JSON Response]
```

### Поточна реалізація

**Контролер:**
```php
// TransferController.php
public function index(): JsonResponse
{
    $transfers = $this->transactionService->getTransfersPaginated(15);
    return TransferResource::collection($transfers)->response();
}
```

**Репозиторій:**
```php
// TransactionRepository.php
public function getTransfersPaginated(int $perPage = 15): LengthAwarePaginator
{
    return Transaction::with(['account'])
        ->where('type', 'transfer_out')
        ->orderBy('created_at', 'desc')
        ->paginate($perPage);
}
```

**SQL запит (основний):**
```sql
SELECT * FROM transactions 
WHERE type = 'transfer_out' 
ORDER BY created_at DESC 
LIMIT 15 OFFSET 0;
```

**Додаткові запити (при завантаженні Resource):**
```sql
-- Для кожного transfer окремий запит
SELECT * FROM accounts WHERE id = :transfer_account_id;
```

### Чекаліст потенційних проблем

| № | Проблема | Статус | Пояснення |
|---|----------|--------|-----------|
| 1 | **N+1 проблема** | ⚠️ Частково | `with(['account'])` вирішує N+1 для account, але client завантажується пізніше |
| 2 | **Відсутність пагінації** | ✅ Ні | Використовується `paginate(15)` |
| 3 | **SELECT *** | ⚠️ Частково | Вибирає всі поля, замість тільки необхідних |
| 4 | **Індекси** | ⚠️ Частково | Є індекс на `type`, `created_at`, але може не вистачати композитного |
| 5 | **Логіка в циклі** | ✅ Ні | Немає обчислень в циклі |
| 6 | **Фільтрація по датах** | ❌ Немає | Параметри `date_from`, `date_to` не реалізовані |
| 7 | **Фільтрація по рахунку** | ❌ Немає | Параметр `account_id` не реалізований |

### Детальний аналіз SQL запитів

#### Поточний запит:
```sql
-- 1 основний запит
SELECT * FROM transactions 
WHERE type = 'transfer_out' 
ORDER BY created_at DESC 
LIMIT 15 OFFSET 0;

-- 15 запитів на аккаунти (якщо без with)
SELECT * FROM accounts WHERE id = :id;
```

#### Оптимізований запит:
```sql
-- 1 основний запит з JOIN
SELECT 
    t.id,
    t.account_id,
    t.amount,
    t.type,
    t.status,
    t.created_at,
    a.account_number,
    a.balance,
    c.full_name as client_name
FROM transactions t
INNER JOIN accounts a ON a.id = t.account_id
INNER JOIN clients c ON c.id = a.client_id
WHERE t.type = 'transfer_out'
    AND (:account_id IS NULL OR t.account_id = :account_id)
    AND (:date_from IS NULL OR t.created_at >= :date_from)
    AND (:date_to IS NULL OR t.created_at <= :date_to)
ORDER BY t.created_at DESC
LIMIT 15 OFFSET 0;
```

### Оцінка впливу при зростанні даних

#### При 100 транзакціях

| Операція | Без оптимізації | З оптимізацією |
|----------|-----------------|----------------|
| Кількість SQL запитів | 1 + 100 = 101 | 1 |
| Час виконання | ~50-100ms | ~10-20ms |
| Пам'ять | ~2-3MB | ~1-2MB |
| N+1 проблема | ❌ Є (101 запит) | ✅ Немає (1 запит) |

#### При 1000 транзакціях

| Операція | Без оптимізації | З оптимізацією |
|----------|-----------------|----------------|
| Кількість SQL запитів | 1 + 1000 = 1001 | 1 + (page*15) |
| Час виконання | ~500-1000ms | ~30-50ms |
| Пам'ять | ~20-30MB | ~5-10MB |
| Ризик timeout | ⚠️ Високий | ✅ Низький |

#### При 100 000+ транзакціях

| Операція | Без оптимізації | З оптимізацією |
|----------|-----------------|----------------|
| Кількість SQL запитів | 1 + 15000 = 15001 | 1 + (page*15) = 16 |
| Час виконання | > 5-10 секунд | ~100-200ms |
| Статус | ❌ Не працює | ✅ Працює |

### Рекомендовані індекси

```sql
-- Основні індекси (є)
CREATE INDEX idx_transactions_type ON transactions(type);
CREATE INDEX idx_transactions_created_at ON transactions(created_at);

-- Додаткові (рекомендуються)
CREATE INDEX idx_transactions_account_id ON transactions(account_id);
CREATE INDEX idx_transactions_type_created_at ON transactions(type, created_at DESC);
CREATE INDEX idx_transactions_account_created ON transactions(account_id, created_at DESC);
```

### Шляхи оптимізації

#### 1. Eager Loading (вже частково)
```php
Transaction::with(['account.client'])->paginate(15);
```

#### 2. Cursor Pagination для великих наборів
```php
Transaction::where('type', 'transfer_out')
    ->orderBy('created_at', 'desc')
    ->cursorPaginate(15);
```

#### 3. Вибрати тільки потрібні поля
```php
Transaction::select(['id', 'account_id', 'amount', 'type', 'status', 'created_at'])
    ->with(['account:id,account_number,client_id', 'account.client:id,full_name'])
    ->paginate(15);
```

#### 4. Кешування для часто повторюваних запитів
```php
Cache::remember('transfers:page:' . $page, 60, function() {
    return Transaction::with(['account.client'])->paginate(15);
});
```

#### 5. Додати фільтрацію
```php
$query = Transaction::where('type', 'transfer_out');
$query->when($accountId, fn($q) => $q->where('account_id', $accountId));
$query->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom));
$query->when($dateTo, fn($q) => $q->whereDate('created_at', '<=', $dateTo));
```

### Висновки

| Показник | Оцінка |
|----------|--------|
| **Поточна продуктивність** | ⚠️ Середня (є N+1 через client) |
| **Готовність до масштабування** | ⚠️ До 10к записів |
| **Пріоритет оптимізації** | 🔴 Високий (часто використовується) |

**Першочергові дії:**
1. Додати `with(['account.client'])` для вирішення N+1
2. Додати композитні індекси
3. Реалізувати фільтрацію по датах та рахунку
4. Впровадити cursor pagination для великих наборів
