# Налаштування queue workers у production

## Вступ

У production середовищі worker'и повинні працювати постійно. При падінні вони повинні автоматично перезапускатися. Для цього використовуються Supervisor (для будь-яких черг) або Horizon (для Redis).

---

## 1. Supervisor

Supervisor — це демон для керування процесами в Linux. Він запускає, стежить і автоматично перезапускає процеси, якщо вони падають.

### Конфігурація для CRM (Redis черги)

Файл: `/etc/supervisor/conf.d/crm-queue.conf`

```ini
[program:crm-queue-redis]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work redis --queue=notifications,audit,default,low,reports --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
numprocs=2
stopasgroup=true
killasgroup=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
redirect_stderr=true

[program:crm-queue-database]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work database --queue=default --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
numprocs=1
stopasgroup=true
killasgroup=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
redirect_stderr=true

[program:crm-scheduler]
command=php /var/www/html/artisan schedule:work
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
redirect_stderr=true
```

### Пояснення параметрів

| Параметр | Значення | Опис |
|----------|----------|------|
| `program` | crm-queue-redis | Ім'я програми |
| `process_name` | `%(program_name)s_%(process_num)02d` | Ім'я процесу (з номером) |
| `command` | `php artisan queue:work ...` | Команда для запуску worker |
| `autostart` | true | Автоматичний запуск при старті supervisor |
| `autorestart` | true | Автоматичний перезапуск при падінні |
| `numprocs` | 2 | Кількість паралельних worker'ів |
| `stopasgroup` | true | Зупиняти всі процеси групи |
| `killasgroup` | true | Вбивати всі процеси групи |
| `stdout_logfile` | `/dev/stdout` | Перенаправлення логів в stdout |
| `redirect_stderr` | true | Перенаправлення stderr в stdout |

### Черги в порядку пріоритету

```bash
--queue=notifications,audit,default,low,reports
```

| Черга | Пріоритет | Що містить |
|-------|-----------|------------|
| `notifications` | Найвищий | Email підтвердження переказів |
| `audit` | Високий | Запис аудиту |
| `default` | Середній | Стандартні задачі |
| `low` | Низький | Оновлення кешу |
| `reports` | Найнижчий | Щоденні звіти |

### Встановлення Supervisor

```bash
# Ubuntu/Debian
sudo apt-get install supervisor

# CentOS/RHEL
sudo yum install supervisor

# Перевірити версію
supervisord -v
```

### Команди Supervisor

```bash
# Перечитати конфігурацію
sudo supervisorctl reread

# Оновити (додати нові програми)
sudo supervisorctl update

# Запустити всі програми
sudo supervisorctl start all

# Запустити конкретну програму
sudo supervisorctl start crm-queue-redis:*

# Зупинити всі програми
sudo supervisorctl stop all

# Перезапустити всі програми
sudo supervisorctl restart all

# Подивитись статус
sudo supervisorctl status

# Подивитись логи
sudo tail -f /var/log/supervisor/crm-queue-redis.log
```

---

## 2. Альтернатива: Cron для Scheduler

Якщо не використовується `schedule:work` в Supervisor, можна використовувати класичний cron:

### Налаштування cron

Додай в crontab:

```bash
* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1
```

### Додавання crontab

```bash
# Відкрити crontab
crontab -e

# Додати рядок
* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1

# Перевірити що додалося
crontab -l
```

### Перевірка

```bash
# Подивитись список запланованих задач
php artisan schedule:list

# Запустити вручну (для тесту)
php artisan schedule:run
```

---

## 3. Laravel Horizon

Horizon — це панель управління та моніторингу для Redis-черг. Забезпечує:

- Дашборд з метриками
- Балансування навантаження
- Автоматичний перезапуск worker'ів
- Моніторинг failed jobs

### Коли використовувати Horizon?

- ✅ Використовується Redis для черг
- ✅ Потрібен дашборд для моніторингу
- ✅ Високе навантаження (багато задач)
- ✅ Потрібне балансування між кількома worker'ами

### Встановлення та налаштування

```bash
# Встановити Horizon
composer require laravel/horizon

# Опублікувати конфігурацію
php artisan horizon:install

# Виконати міграції (для зберігання метрик)
php artisan migrate
```

### Конфігурація Horizon

`config/horizon.php`

```php
'environments' => [
    'production' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['notifications', 'audit', 'default', 'low', 'reports'],
            'balance' => 'auto',
            'processes' => 10,
            'tries' => 3,
            'timeout' => 60,
        ],
    ],
],
```

### Supervisor для Horizon

```ini
[program:crm-horizon]
process_name=%(program_name)s
command=php /var/www/html/artisan horizon
autostart=true
autorestart=true
user=www-data
numprocs=1
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
redirect_stderr=true
stopwaitsecs=3600
```

### Horizon дашборд

Відкрий в браузері: `http://your-domain/horizon`

---

## 4. Чекліст перед деплоєм

### Перед запуском

- [ ] Перевірити `QUEUE_CONNECTION=redis` в `.env`
  ```bash
  grep QUEUE_CONNECTION .env
  # Повинно бути: QUEUE_CONNECTION=redis
  ```

- [ ] Перевірити що Redis запущений
  ```bash
  redis-cli PING
  # Відповідь: PONG
  ```

- [ ] Перевірити що таблиці `jobs` і `failed_jobs` створені
  ```bash
  php artisan queue:table
  php artisan migrate
  ```

- [ ] Налаштувати Supervisor
  ```bash
  sudo cp /path/to/crm-queue.conf /etc/supervisor/conf.d/
  sudo supervisorctl reread
  sudo supervisorctl update
  ```

- [ ] Налаштувати cron для Scheduler (якщо не використовується `schedule:work`)
  ```bash
  crontab -e
  * * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1
  ```

### Після запуску

- [ ] Перевірити що worker запущений
  ```bash
  sudo supervisorctl status
  ```

- [ ] Перевірити що немає failed jobs
  ```bash
  php artisan queue:failed
  ```

- [ ] Перевірити логи
  ```bash
  sudo tail -f /var/log/supervisor/crm-queue-redis.log
  ```

- [ ] Налаштувати моніторинг failed jobs (cron)
  ```bash
  0 * * * * cd /var/www/html && php artisan queue:failed --json >> /var/log/queue-failed.log 2>&1
  ```

---

## 5. Порівняння: Supervisor vs Horizon

| Аспект | Supervisor | Horizon |
|--------|------------|---------|
| **Драйвер черги** | Будь-який (database, redis, sqs) | Тільки Redis |
| **Дашборд** | Немає | Так (красивий UI) |
| **Балансування** | Ручне (numprocs) | Автоматичне |
| **Метрики** | Немає | Так (частота, час виконання) |
| **Складність** | Простий | Середній |
| **Коли використовувати** | Будь-який проект | Redis + потрібен моніторинг |

---

## 6. Рекомендації по середовищах

| Середовище | Рекомендація |
|------------|--------------|
| **Локальна розробка** | `php artisan queue:work` (без Supervisor) |
| **Docker локально** | `docker compose exec app php artisan queue:work` |
| **Staging / невеликий проект** | Supervisor |
| **Production з Redis** | Supervisor або Horizon |
| **Production з великим навантаженням** | Horizon |

---

## 7. Швидка довідка команд

### Supervisor

```bash
# Статус всіх процесів
sudo supervisorctl status

# Запустити всі
sudo supervisorctl start all

# Зупинити всі
sudo supervisorctl stop all

# Перезапустити всі
sudo supervisorctl restart all

# Перечитати конфіг
sudo supervisorctl reread

# Оновити конфіг
sudo supervisorctl update
```

### Черги

```bash
# Запустити worker вручну
php artisan queue:work redis --queue=notifications,audit,default,low,reports --sleep=3 --tries=3

# Перевірити failed jobs
php artisan queue:failed

# Повторити failed jobs
php artisan queue:retry all

# Видалити failed jobs
php artisan queue:flush
```

### Scheduler

```bash
# Список задач
php artisan schedule:list

# Запустити вручну
php artisan schedule:run

# Запустити в режимі роботи (заміна cron)
php artisan schedule:work
```
