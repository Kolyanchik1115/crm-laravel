# Чекліст перед деплоєм черг (Queues)

## Перед запуском

### 1. Перевірити налаштування `.env`

```bash
grep QUEUE_CONNECTION .env
```

**Очікуваний результат:**

```
QUEUE_CONNECTION=redis
```

### 2. Перевірити що Redis запущений

```bash
# Docker
docker compose exec redis redis-cli PING

# Локально
redis-cli PING
```

**Очікуваний результат:**

```
PONG
```

### 3. Перевірити що таблиці черг створені

```bash
# Docker
docker compose exec mysql mysql -u crm_user -p -e "SHOW TABLES LIKE '%job%';"

# Локально
mysql -u root -p -e "SHOW TABLES FROM crm_db LIKE '%job%';"
```

**Очікуваний результат:**

```
jobs
failed_jobs
```

### 4. Перевірити що worker запущений

```bash
# Docker
docker compose exec app php artisan queue:work redis --queue=notifications,audit,default,low --sleep=3 --tries=3

# Локально
php artisan queue:work redis --queue=notifications,audit,default,low --sleep=3 --tries=3
```

**Очікуваний результат:**

```
INFO  Processing jobs from the [notifications,audit,default,low] queues.
```

## Після запуску

### 5. Перевірити що немає failed jobs

```bash
# Docker
docker compose exec app php artisan queue:failed

# Локально
php artisan queue:failed
```

**Очікуваний результат:**

```
INFO  No failed jobs found.
```

### 6. Перевірити що черги працюють

```bash
# Docker
docker compose exec redis redis-cli KEYS "*queue*"

# Локально
redis-cli KEYS "*queue*"
```

**Очікуваний результат:**

```
crm-finance-database-queues:low:delayed
(або інші черги, якщо є задачі)
```

### 7. Перевірити що Job виконуються

```bash
# Docker
docker compose exec app tail -50 storage/logs/laravel.log | grep -E "SendTransferConfirmationJob|LogInvoiceAuditJob|UpdateDashboardCacheJob"

# Локально
tail -50 storage/logs/laravel.log | grep -E "SendTransferConfirmationJob|LogInvoiceAuditJob|UpdateDashboardCacheJob"
```

**Очікуваний результат:**

```
local.INFO: SendTransferConfirmationJob: Completed successfully
local.INFO: LogInvoiceAuditJob: Completed successfully
local.INFO: UpdateDashboardCacheJob: Completed successfully
```

## Проблеми та рішення

| Проблема                   | Рішення                                                                |
|----------------------------|------------------------------------------------------------------------|
| Worker не запущений        | `php artisan queue:work redis --queue=notifications,audit,default,low` |
| Job не потрапляють в чергу | Перевірити `onQueue()` в dispatch                                      |
| Failed jobs                | `php artisan queue:retry all`                                          |
| Черги переповнені          | `php artisan queue:clear redis`                                        |
| Redis не відповідає        | Перевірити `REDIS_HOST` в `.env`                                       |
| База даних не доступна     | Перевірити `DB_HOST` в `.env`                                          |

## Корисні команди

```bash
# Перегляд failed jobs
php artisan queue:failed

# Деталі failed job
php artisan queue:failed <id>

# Повторення всіх failed jobs
php artisan queue:retry all

# Видалення всіх failed jobs
php artisan queue:flush

# Очищення черги
php artisan queue:clear redis

# Перезапуск worker
php artisan queue:restart
```

## Швидкий чекліст (перед деплоєм)

- [ ] `QUEUE_CONNECTION=redis` в `.env`
- [ ] Redis працює (`PONG`)
- [ ] Таблиці `jobs` і `failed_jobs` існують
- [ ] Worker запущений
- [ ] Немає failed jobs
- [ ] Логи Job успішні
