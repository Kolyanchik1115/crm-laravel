# Життєвий цикл HTTP-запиту в Laravel

## 7 кроків життєвого циклу запиту в Laravel

### Крок 1: Запит потрапляє в public/index.php

Всі запити до Laravel-додатку спрямовуються у файл `public/index.php`. Це **єдина точка входу** (front controller) для кожного запиту.

**Що відбувається:**
- Веб-сервер (Apache/Nginx) перенаправляє всі запити до цього файлу
- Жодні інші PHP-файли не доступні напряму

**Порівняння з Module 3:**
- У чистому PHP ми мали багато точок входу: `public/clients.php`, `public/invoices.php`, `public/accounts.php`
- Laravel має ОДНУ точку входу для всього

---

### Крок 2: Ініціалізація додатку (Bootstrap)

Laravel ініціалізує фреймворк:

1. **Завантажується автозавантажувач** - Composer підключає всі класи
2. **Створюється Service Container** - створюється екземпляр додатку
3. **Завантажується оточення** - змінні з `.env` завантажуються
4. **Завантажується конфігурація** - всі файли з папки `/config` завантажуються
5. **Реєструються Service Providers** - провайдери з `config/app.php` реєструються

**Порівняння з Module 3:**
- Ми вручну підключали файли через `require_once`
- Ми вручну завантажували `config/database.php` та `config/redis.php`
- Laravel робить це автоматично

---

### Крок 3: HTTP Kernel та Middleware

Запит проходить через **HTTP Kernel** (`app/Http/Kernel.php`):

1. **Глобальні middleware** - всі запити проходять через:
   - `\App\Http\Middleware\TrustProxies`
   - `\App\Http\Middleware\PreventRequestsDuringMaintenance`
   - `\App\Http\Middleware\TrimStrings`
   - `\App\Http\Middleware\ConvertEmptyStringsToNull`

2. **Групи middleware** - групи на кшталт `web` або `api` додають свої middleware (сесії, cookies, CSRF)

**Порівняння з Module 3:**
- У нас не було концепції middleware
- Автентифікація, логування, CORS оброблялися вручну в кожному файлі
- Laravel централізує цю логіку

---

### Крок 4: Маршрутизація (Routing)

**Роутер** визначає, який контролер або closure оброблятиме запит:

1. Роутер порівнює URL з визначеними маршрутами в `routes/web.php` або `routes/api.php`
2. Роутер обробляє параметри маршруту (наприклад, `{id}` з `/invoices/{id}`)
3. Роутер застосовує модельне зв'язування (якщо налаштовано)

**Приклад:**
```
GET /clients → збігається з Route::get('/clients', ...)
GET /invoices/5 → збігається з Route::get('/invoices/{id}', ...)
```

**Порівняння з Module 3:**
- У чистому PHP маршрутизація була ручною: `if ($_GET['id'])` або прямий доступ до файлів
- Laravel має централізовану, декларативну маршрутизацію

---

### Крок 5: Route Middleware та Контролер

Перед виконанням маршруту:

1. **Специфічні для маршруту middleware** виконуються (наприклад, `auth`, `verified`)
2. **Валідація запиту** (якщо використовуються Form Requests)
3. **Впровадження залежностей** - Laravel автоматично впроваджує необхідні залежності

Потім виконується **метод контролера** або **closure**:

```php
// Приклад closure
use Modules\Client\src\Interfaces\Http\Api\V1\ClientController;Route::get('/clients', function () {
    return 'Сторінка клієнтів';
});

// Приклад контролера
Route::get('/clients', [ClientController::class, 'index']);
```

**Порівняння з Module 3:**
- У чистому PHP ми викликали функції напряму: `$stmt->fetchAll()`, тощо
- Laravel використовує контролери з автоматичним впровадженням залежностей

---

### Крок 6: Формування відповіді (Response Generation)

Контролер/closure повертає відповідь:

- **Рядок** → конвертується в HTML відповідь
- **Масив** → автоматично перетворюється в JSON
- **View** → рендериться Blade-шаблон
- **JsonResponse** → відповідь для API
- **Redirect** → перенаправлення на інший URL

```php
// Рядкова відповідь
return 'Привіт, світ!';

// JSON відповідь (масив автоматично конвертується)
return ['name' => 'Іван'];

// Відповідь з шаблоном
return view('clients.index');

// Явна JSON відповідь
return response()->json(['name' => 'Іван']);
```

**Порівняння з Module 3:**
- У чистому PHP ми вручну виводили HTML та використовували `json_encode()`
- Laravel надає різні типи відповідей

---

### Крок 7: Відправка відповіді клієнту

1. **Виконуються terminable middleware** - операції очищення
2. **Відповідь надсилається** - Laravel надсилає заголовки та вміст у браузер
3. **Логування** - запит логується (якщо налаштовано)

Користувач бачить сторінку у своєму браузері.

---

## Візуальна схема

```
Запит від браузера
       ↓
public/index.php (Єдина точка входу)
       ↓
Bootstrap та Service Container
       ↓
HTTP Kernel (app/Http/Kernel.php)
       ↓
Глобальні middleware (TrimStrings, тощо)
       ↓
Роутер (routes/web.php або routes/api.php)
       ↓
Middleware маршруту (auth, тощо)
       ↓
Контролер / Closure
       ↓
Формування відповіді
       ↓
Відповідь надіслано в браузер
```
