
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
