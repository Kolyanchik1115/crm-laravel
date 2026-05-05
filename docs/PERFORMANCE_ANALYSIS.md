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
