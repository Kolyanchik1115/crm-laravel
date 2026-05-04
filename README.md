# CRM Finance

## 📬 Postman колекція

### Документація API (перегляд)

- [Swagger Documentation](http://localhost:8000/swagger)
- [Swagger YAML](http://localhost:8000/api-docs/openapi.yaml)
- [Health Check](http://localhost:8000/health)

### Імпорт колекції через файл

1. Завантаж файл [postman_collection.json](postman_collection.json)
2. Відкрий Postman → **Import** → обрати файл
3. Встанови змінну `base_url` (локально: `http://localhost:8000`, на сервері: `https://your-domain.com`)

## 🏗️ Архітектура проекту

Проект побудовано на **Clean Modular Monolith** архітектурі - гібрид модульного моноліту та чистої архітектури.

### Що змінилося?

| Компонент | Раніше | Тепер |
|-----------|--------|-------|
| **Структура** | Монолітна (app/Models, app/Http) | Модульна (modules/) |
| **Бізнес-логіка** | В контролерах/моделях | В Domain/Application шарах |
| **Аутентифікація** | Sanctum/Session | JWT + Roles |
| **Роути** | В routes/web.php | В кожному модулі |
| **Тести** | В tests/ | В модулях (tests/) |

### Структура модулів

```
modules/
├── Auth/          # Аутентифікація, JWT, ролі (ADMIN, MANAGER, USER)
├── Client/        # Управління клієнтами
├── Account/       # Банківські рахунки
├── Transaction/   # Транзакції та перекази (+ комісія 0.5%)
├── Invoice/       # Інвойси, рахунки-фактури
├── Service/       # Справочник послуг
├── Dashboard/     # Дашборд та статистика (кэш)
└── Shared/        # Спільні компоненти (Value Objects, Traits, Middleware)
```

## 🚀 Як створити проект

```bash
# Створити новий Laravel проект
composer create-project laravel/laravel crm-laravel

# Перейти в папку проекту
cd crm-laravel
```

## 🐳 Docker

### Запуск контейнерів

```bash
docker compose up -d
```

### Міграції в Docker

```bash
docker compose exec app php artisan migrate
```
---

## ⚙️ Як налаштувати .env

### 1. Скопіювати файл .env.example

```bash
cp .env.example .env
```

### 2. Згенерувати ключ додатку

```bash
php artisan key:generate
```

### 3. Налаштувати підключення до бази даних

Відкрийте файл `.env` та заповніть параметри для бази даних:

#### Для локальної MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crm_db
DB_USERNAME=root
DB_PASSWORD=
```

#### Для Docker MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=crm_db
DB_USERNAME=root
DB_PASSWORD=root
```

### 4. JWT налаштування

```bash
# Згенерувати JWT секрет
docker compose exec app php artisan jwt:secret
```
---

## 🗄️ Міграції та сидери

```bash
# Виконати всі міграції
docker compose exec app php artisan migrate

# Очистити базу, міграції + сидери
docker compose exec app php artisan migrate:fresh --seed

# Виконати конкретний сидер
docker compose exec app php artisan db:seed --class=RolePermissionSeeder
```

## 🏃 Як запустити проект

```bash
docker compose exec app php artisan serve
```

Сервер буде доступний за адресою: **http://localhost:8000**

### Запуск worker (обробка черг)

```bash
docker compose exec app php artisan queue:work --sleep=3 --tries=3
```

### Перевірка Redis

```bash
docker compose exec redis redis-cli PING
# Відповідь: PONG
```

### Зупинка контейнерів

```bash
docker compose down
```

### Перегляд логів

```bash
docker compose logs app -f
docker compose logs queue -f
```

---

## 🧪 Тестування

```bash
# Всі тести
docker compose exec app php artisan test

# Тест модуля
docker compose exec app php artisan test modules/Transaction/tests/Feature/Api/TransferApiTest.php
```

---

## 🔐 API Ендпоінти

### Аутентифікація

| Метод | URL | Опис |
|-------|-----|------|
| POST | `/api/v1/auth/login` | Логін |
| POST | `/api/v1/auth/register` | Реєстрація |
| GET | `/api/v1/auth/me` | Поточний користувач |
| POST | `/api/v1/auth/logout` | Вихід |
| POST | `/api/v1/auth/refresh` | Оновити токен |

### Ресурси (захищені JWT)

| Метод | URL | Ролі |
|-------|-----|------|
| GET | `/api/v1/clients` | ADMIN, MANAGER |
| GET | `/api/v1/accounts` | ADMIN, MANAGER, USER |
| POST | `/api/v1/transfers` | ADMIN, MANAGER, USER |
| GET | `/api/v1/invoices` | ADMIN, MANAGER |
| PATCH | `/api/v1/invoices/{id}` | ADMIN, MANAGER |
| GET | `/api/v1/services` | ADMIN, MANAGER, USER |

---

## 📊 Логування та моніторинг

### Обсервери (Observers)

Автоматичні дії при подіях з моделями:

| Обсервер | Подія | Дія |
|----------|-------|-----|
| **UserObserver** | `created` | Логування створення користувача |
| | `updated` | Логування змін |
| | `deleting` | Захист від видалення останнього адміна |
| **ClientObserver** | `created` | Автоматичне створення рахунку для клієнта |
| | `deleted` | Логування видалення |
| **InvoiceObserver** | `creating` | Генерація унікального номера інвойса |
| | `deleting` | Заборона видалення оплачених інвойсів |

### Логування

#### Локальні логи

```bash
# Перегляд логів
tail -f storage/logs/laravel.log

# Логи в Docker
docker compose logs app -f
docker compose logs queue -f
```

#### Структура логування

```php
Log::info('User created', [
    'user_id' => $user->id,
    'email' => $user->email,
    'correlation_id' => $correlationId,
]);
```

### Sentry (Помилки)

Помилки автоматично відправляються в Sentry:

```env
SENTRY_LARAVEL_DSN=https://your-dsn@sentry.io/project
```

#### Тестовий ендпоінт для Sentry

```bash
# Тільки в dev середовищі
GET /test-sentry
```

### Correlation ID

Кожен запит отримує унікальний `X-Correlation-Id` для трекінгу:

- Автоматично генерується, якщо не переданий
- Додається в логи всіх запитів
- Передається у відповідь

### Моніторинг черг (Queues)

```bash
# Перевірка статусу черг
docker compose exec app php artisan queue:failed
docker compose exec app php artisan queue:monitor redis,default

# Health check
GET /health
```
---

## 📚 Документація

### 🏗️ Архітектура

- [📐 STRUCTURE.md](docs/STRUCTURE.md) - детальний опис структури проекту
- [📐 ARCHITECTURE.md](docs/ARCHITECTURE.md) - Архітектура CRM
- [🔄 LIFECYCLE.md](docs/LIFECYCLE.md) - життєвий цикл HTTP-запиту
- [⚖️ SYMFONY_VS_LARAVEL.md](docs/SYMFONY_VS_LARAVEL.md) - порівняння Laravel та Symfony
- [📡 ASYNC_ARCHITECTURE.md](docs/ASYNC_ARCHITECTURE.md) - Jobs vs Events vs Notifications

### 📨 Черги (Queues)

- [📋 QUEUE_SCENARIOS.md](docs/QUEUE_SCENARIOS.md) - сценарії використання черг
- [📋 QUEUE_CHECKLIST.md](docs/QUEUE_CHECKLIST.md) - сценарії перед деплоєм
- [⚙️ QUEUE_SETUP.md](docs/QUEUE_SETUP.md) - налаштування черг у CRM
- [🚀 QUEUE_DRIVERS.md](docs/QUEUE_DRIVERS.md) - порівняння драйверів черг
- [⚠️ QUEUE_ANTIPATTERNS.md](docs/QUEUE_ANTIPATTERNS.md) - коли НЕ використовувати черги
- [🔄 JOB_LIFECYCLE.md](docs/JOB_LIFECYCLE.md) - життєвий цикл Job
- [🏭 QUEUE_PRODUCTION.md](docs/QUEUE_PRODUCTION.md) - налаштування worker'ів у production

### 🧪 Тестування

- [🔄 TESTING.md](docs/TESTING.md) - інструкції до тестування

### 🏗️ API

- [🔄 API_CONTRACT.md](docs/API_CONTRACT.md) - контракти API
- [🔄 API_STATUS_CODES.md](docs/API_STATUS_CODES.md) - статус коди
- [🔄 API_MAINTENANCE.md](docs/API_MAINTENANCE.md) - обслуговування API

### 🚀 Логування та моніторинг

- [🔄 LOGGING_GUIDE.md](docs/LOGGING_GUIDE.md) - гайд з логування
---

## 📝 Команди для розробки

```bash
# Очистити кеш
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan view:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan config:clear

# Перевірити роути
docker compose exec app php artisan route:list

# Обновити автолоадинг
docker compose exec app composer dump-autoload
```
