# Життєвий цикл Job у Laravel

## Що таке Job?

Job — це задача, яка виконується **асинхронно** у фоновому режимі, не блокуючи користувача.

---

## Життєвий цикл Job

### 1: Диспетчеризація (dispatch)

У коді викликається відправка задачі:

```php
// В контролері або сервісі
SendWelcomeEmail::dispatch($client);
// або
dispatch(new SendWelcomeEmail($client));
```

---

### 2: Серіалізація та збереження

Laravel серіалізує Job (перетворює в рядок) і зберігає в чергу:

| Драйвер | Куди зберігається |
|---------|-------------------|
| `database` | Таблиця `jobs` |
| `redis` | Redis (ключ `queues:default`) |
| `sqs` | AWS SQS |

```sql
-- Приклад запису в таблицю jobs
INSERT INTO jobs (queue, payload, attempts, available_at)
VALUES ('default', '{"displayName":"SendWelcomeEmail",...}', 0, 1234567890);
```

---

### 3: HTTP-відповідь клієнту

Після збереження Job у чергу, Laravel **миттєво** повертає відповідь користувачу.

```
Користувач не чекає виконання Job! Відповідь приходить за ~50-100ms.
```

---

### 4: Worker забирає Job

Окремий процес `php artisan queue:work` постійно перевіряє чергу:

```bash
php artisan queue:work redis --sleep=3 --tries=3
```

Worker:
1. Запитує чергу: "Чи є нові задачі?"
2. Якщо є — забирає Job
3. Якщо немає — чекає `--sleep` секунд

---

### 5: Десеріалізація та виконання

Worker десеріалізує Job і викликає метод `handle()`:

```php
class SendWelcomeEmail implements ShouldQueue
{
    public function handle(): void
    {
        // Цей код виконується у worker процесі
        Mail::to($this->client->email)->send(new WelcomeMail($this->client));
    }
}
```

---

### 6: Результат виконання

| Результат | Що відбувається |
|-----------|-----------------|
| **Успіх** | Job видаляється з черги |
| **Помилка** | `attempts` збільшується; повторна спроба (до `--tries=3`) |
| **3 невдалі спроби** | Job переміщується в `failed_jobs` |

---

## Діаграма життєвого циклу

### Текстова схема:

```
HTTP-запит (користувач)
        │
        ▼
┌───────────────┐
│  Controller   │
│  dispatch()   │
└───────────────┘
        │
        ▼
┌───────────────┐     ┌─────────────────┐
│ Серіалізація  │────▶│  Сховище черги  │
│ та збереження │     │  (jobs / Redis) │
└───────────────┘     └─────────────────┘
        │                      │
        ▼                      │
┌───────────────┐              │
│  Відповідь    │              │
│  користувачу  │              │
└───────────────┘              │
                               │
        ┌──────────────────────┘
        │
        ▼
┌───────────────┐
│    Worker     │ ◀─── php artisan queue:work
│   забирає Job │
└───────────────┘
        │
        ▼
┌───────────────┐
│ Десеріалізація│
│   handle()    │
└───────────────┘
        │
        ├──▶ Успіх ──▶ Видалення з черги
        │
        └──▶ Помилка ──▶ Retry (3 рази) ──▶ failed_jobs
```

## Що станеться, якщо worker не запущений?

| Сценарій | Результат |
|----------|-----------|
| **Job відправлена** | ✅ Збережена в черзі |
| **Користувач** | ✅ Отримав відповідь |
| **Worker** | ❌ Не запущений |
| **Job** | ❌ Не виконується (накопичується) |
| **Після запуску worker** | ✅ Виконає всі накопичені задачі |

### Перевірка накопичених задач:

```bash
# Для database драйвера
docker compose exec mysql mysql -u crm_user -p -e "SELECT COUNT(*) FROM crm_db.jobs;"

# Для redis драйвера
docker compose exec redis redis-cli LLEN queues:default
```

---

## Практичний приклад

### Створення Job:

```bash
php artisan make:job SendWelcomeEmail
```

```php
class SendWelcomeEmail implements ShouldQueue
{
    use Queueable;

    public function __construct(public Client $client) {}

    public function handle(): void
    {
        Mail::to($this->client->email)->send(new WelcomeMail($this->client));
    }
}
```

### Відправка в чергу:

```php
// В контролері
SendWelcomeEmail::dispatch($client);
```

### Запуск worker:

```bash
# В окремому терміналі
php artisan queue:work redis --sleep=3 --tries=3
```

### Перевірка failed jobs:

```bash
php artisan queue:failed
php artisan queue:retry all
php artisan queue:flush
```

---

## Висновок

| Крок | Відповідальний процес | Блокує користувача? |
|------|----------------------|---------------------|
| 1-3 | HTTP запит (php artisan serve) | ✅ Так (до збереження в чергу) |
| 4-6 | Worker (php artisan queue:work) | ❌ Ні (фоновий) |
