# CRM Finance - Laravel Project

## 📬 Postman колекція

### Документація API (перегляд)

- [Postman Documentation](https://documenter.getpostman.com/view/24380148/2sBXitCnNR)
- [Swagger Documentation](http://localhost:8000/swagger)

### Імпорт колекції через файл

1. Завантаж файл [postman_collection.json](postman/postman_collection.json)
2. Відкрий Postman → **Import** → обрати файл
3. Встанови змінну `base_url` (локально: `http://localhost:8000`, на сервері: `https://your-domain.com`)

## 🚀 Як створити проект

```bash
# Створити новий Laravel проект
composer create-project laravel/laravel crm-laravel

# Перейти в папку проекту
cd crm-laravel
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
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crm_db
DB_USERNAME=crm_user
DB_PASSWORD=crm_password
```

---

## 🗄️ Міграції та сидери

### Створення таблиць (міграції)

Міграції створюють структуру таблиць в базі даних:

```bash
# Виконати всі міграції
php artisan migrate

# Очистити базу і виконати всі міграції заново
php artisan migrate:fresh
```

### Заповнення даними (сидери)

Сидери наповнюють таблиці тестовими даними:

```bash
# Виконати всі сидери
php artisan db:seed

# Виконати конкретний сидер
php artisan db:seed --class=ClientsTableSeeder

# Очистити базу, виконати міграції та сидери (все разом)
php artisan migrate:fresh --seed
```

## 🏃 Як запустити проект

```bash
php artisan serve
```

Сервер буде доступний за адресою: **http://localhost:8000**

Щоб зупинити сервер, натисніть `Ctrl + C`.

---

## 🌐 Маршрути проекту

| URL             | Опис                                |
|-----------------|-------------------------------------|
| `/`             | Головна сторінка (Laravel welcome)  |
| `/clients`      | Сторінка клієнтів (заглушка)        |
| `/accounts`     | Сторінка рахунків (заглушка)        |
| `/transactions` | Сторінка транзакцій (заглушка)      |
| `/invoices`     | Сторінка рахунків-фактур (заглушка) |
| `/crm-settings` | Перевірка CRM налаштувань (JSON)    |

---

### Перевірка маршрутів:

```bash
php artisan route:list
```

---

## 📁 Структура проекту

| Папка        | Призначення                                   |
|--------------|-----------------------------------------------|
| `app/`       | Ядро додатку (контролери, моделі, middleware) |
| `bootstrap/` | Завантажувач фреймворку                       |
| `config/`    | Конфігураційні файли                          |
| `database/`  | Міграції, сіди, фабрики                       |
| `public/`    | Точка входу (index.php)                       |
| `resources/` | Шаблони (Blade), мови, assets                 |
| `routes/`    | Визначення маршрутів                          |
| `storage/`   | Логи, кеш, завантажені файли                  |
| `tests/`     | Тести                                         |

---

## 🐳 Docker

### Запуск контейнерів

```bash
# Запустити всі сервіси (MySQL, Redis, Laravel, Queue)
docker compose up -d
```

### Виконання міграцій в Docker

```bash
docker compose exec app php artisan migrate
```

### Запуск worker (обробка черг)

```bash
docker compose exec app php artisan queue:work redis --sleep=3 --tries=3
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
docker compose logs app
docker compose logs queue
docker compose logs redis
```

---

## 📚 Документація

### 🏗️ Архітектура

- [📐 STRUCTURE.md](docs/STRUCTURE.md) - детальний опис структури проекту
- [🔄 LIFECYCLE.md](docs/LIFECYCLE.md) - життєвий цикл HTTP-запиту в Laravel
- [⚖️ SYMFONY_VS_LARAVEL.md](docs/SYMFONY_VS_LARAVEL.md) - порівняння Laravel та Symfony
- [📡 ASYNC_ARCHITECTURE.md](docs/ASYNC_ARCHITECTURE.md) - Jobs vs Events vs Notifications
- [📐ARCHITECTURE.md](docs/ARCHITECTURE.md) - Архітектура CRM

### 📨 Черги (Queues)

- [📋 QUEUE_SCENARIOS.md](docs/QUEUE_SCENARIOS.md) - сценарії використання черг
- [📋 QUEUE_CHECKLIST.md](docs/QUEUE_CHECKLIST.md) - сценарії перед деплоєм
- [⚙️ QUEUE_SETUP.md](docs/QUEUE_SETUP.md) - налаштування черг у CRM
- [🚀 QUEUE_DRIVERS.md](docs/QUEUE_DRIVERS.md) - порівняння драйверів черг
- [⚠️ QUEUE_ANTIPATTERNS.md](docs/QUEUE_ANTIPATTERNS.md) - коли НЕ використовувати черги
- [🔄 JOB_LIFECYCLE.md](docs/JOB_LIFECYCLE.md) - життєвий цикл Job у Laravel
- [🏭 QUEUE_PRODUCTION.md](docs/QUEUE_PRODUCTION.md) - налаштування worker'ів у production

### 📨 Тестування

- [🔄 TESTING.md](docs/TESTING.md) - iнструкцiї до тестування

### 🏗️ API

- [🔄 API_CONTRACT](docs/API_CONTRACT.md) - iнструкцiї до api
- [🔄 API_STATUS_CODES](docs/API_STATUS_CODES.md) - cтатус коди
- [🔄 API_MAINTENANCE](docs/API_MAINTENANCE.md) - обслуговування API

