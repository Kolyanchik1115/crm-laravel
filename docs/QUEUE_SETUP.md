
# Налаштування черг (Queues) у CRM

## 1. Конфігурація в .env

```env
# Основний драйвер черги
QUEUE_CONNECTION=database
```

## 2. Драйвери черг

| Драйвер | Команда | Коли використовувати |
|---------|---------|---------------------|
| **sync** | (без worker) | Локальна розробка - виконує задачі синхронно, без черги |
| **database** | `php artisan queue:work database` | Без Redis, для продакшену на невеликих проектах |
| **redis** | `php artisan queue:work redis` | Продакшен, високе навантаження |
| **sqs** | `php artisan queue:work sqs` | AWS, великі проекти |
| **beanstalkd** | `php artisan queue:work beanstalkd` | Класичний черговий сервер |

## 3. Як перемикатися між драйверами

### На локальну розробку (синхронно - без черги)

```env
QUEUE_CONNECTION=sync
```

### На database (для тестування черг без Redis)

```env
QUEUE_CONNECTION=database
php artisan queue:work database
```

### На Redis (для продакшену)

```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
php artisan queue:work redis
```

## 4. Створення таблиць для черг

```bash
# Створити міграції
php artisan queue:table
php artisan queue:failed-table

# Виконати міграції
php artisan migrate
```

### Таблиця `jobs`

| Поле | Тип | Опис |
|------|-----|------|
| id | bigint | PRIMARY KEY AUTO_INCREMENT |
| queue | varchar(255) | Ім'я черги (default) |
| payload | longtext | Серіалізована задача (Job) |
| attempts | tinyint | Кількість спроб виконання |
| reserved_at | int | Час резервування (NULL) |
| available_at | int | Час, коли задача доступна |
| created_at | int | Час створення |

### Таблиця `failed_jobs`

| Поле | Тип | Опис |
|------|-----|------|
| id | bigint | PRIMARY KEY AUTO_INCREMENT |
| uuid | varchar(255) | Унікальний ідентифікатор |
| connection | text | Ім'я з'єднання |
| queue | text | Ім'я черги |
| payload | longtext | Серіалізована задача |
| exception | longtext | Текст помилки |
| failed_at | timestamp | Час помилки |

## 5. Запуск worker'а

### Запустити один раз (для тестування)

```bash
php artisan queue:work database
```

### Запустити з параметрами (для продакшену)

```bash
php artisan queue:work database --sleep=3 --tries=3 --max-jobs=1000
```

| Параметр | Значення | Опис |
|----------|----------|------|
| `--sleep=3` | 3 секунди | Час очікування, якщо немає задач |
| `--tries=3` | 3 спроби | Кількість повторів при помилці |
| `--max-jobs=1000` | 1000 | Максимум задач до перезапуску |

### Запустити в фоновому режимі (для продакшену)

```bash
nohup php artisan queue:work database --sleep=3 --tries=3 > storage/logs/queue.log 2>&1 &
```

### Supervisor (рекомендовано для продакшену)

`/etc/supervisor/conf.d/laravel-worker.conf`

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/crm-laravel/artisan queue:work database --sleep=3 --tries=3
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=forge
numprocs=8
redirect_stderr=true
stdout_logfile=/path/to/crm-laravel/storage/logs/worker.log
stopwaitsecs=3600
```

## 6. Команди для роботи з чергами

```bash
# Запустити worker
php artisan queue:work database

# Обробити всі задачі (один раз)
php artisan queue:work database --once

# Очистити чергу
php artisan queue:clear database

# Перезапустити worker
php artisan queue:restart

# Показати невдалі задачі
php artisan queue:failed

# Повторити невдалу задачу
php artisan queue:retry all

# Видалити невдалі задачі
php artisan queue:flush

# Показати кількість задач
php artisan queue:monitor default
```

## Висновок

| Драйвер | Переваги | Недоліки |
|---------|----------|----------|
| **sync** | Просто, без залежностей | Задачі виконуються синхронно |
| **database** | Не потребує Redis, легко | Менша продуктивність |
| **redis** | Швидко, масштабується | Потребує Redis |


# Черги та пріоритети

## Як працює onQueue()

Метод `onQueue()` дозволяє відправити Job в конкретну чергу:

```php
// Відправка в чергу notifications
use Modules\Invoice\Application\Jobs\LogInvoiceAuditJob;SendTransferConfirmationJob::dispatch($id)->onQueue('notifications');

// Відправка в чергу audit
LogInvoiceAuditJob::dispatch($id)->onQueue('audit');

// Відправка в чергу low з затримкою
UpdateDashboardCacheJob::dispatch()->onQueue('low')->delay(now()->addSeconds(30));
```

### Пріоритети черг в CRM

| Черга | Пріоритет | Job | Чому |
|-------|-----------|-----|------|
| `notifications` | Високий | `SendTransferConfirmationJob` | Клієнт очікує email підтвердження |
| `audit` | Середній | `LogInvoiceAuditJob` | Важливо для історії, але не терміново |
| `default` | Середній | Інші Job | Стандартні задачі |
| `low` | Низький | `UpdateDashboardCacheJob` | Не критично, можна зачекати |

### Запуск worker з декількома чергами

```bash
# Docker
docker compose exec app php artisan queue:work redis --queue=notifications,audit,default,low --sleep=3 --tries=3

# Локально
php artisan queue:work redis --queue=notifications,audit,default,low --sleep=3 --tries=3
```

Worker обробляє черги в заданому порядку:
1. Спочатку всі задачі з `notifications`
2. Потім з `audit`
3. Потім з `default`
4. В останню чергу з `low`

### Перевірка черг в Redis

```bash
# Docker
docker compose exec redis redis-cli KEYS "*queue*"
docker compose exec redis redis-cli LLEN queues:notifications
docker compose exec redis redis-cli LLEN queues:audit
docker compose exec redis redis-cli LLEN queues:low

# Локально
redis-cli KEYS "*queue*"
redis-cli LLEN queues:notifications
```

### Приклад порядку обробки

1. Клієнт робить переказ → Job потрапляє в чергу `notifications`
2. Worker бере Job з `notifications` → відправляє email
3. Потім бере Job з `audit` → записує аудит
4. Потім бере Job з `low` → оновлює кеш (через 30 секунд)

### Переваги використання різних черг

- **Пріоритезація** — важливі задачі виконуються першими
- **Ізоляція** — помилка в одній черзі не блокує інші
- **Масштабування** — можна запустити окремі worker для різних черг
