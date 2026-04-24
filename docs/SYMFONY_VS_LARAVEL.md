# Порівняння структур Laravel та Symfony

Laravel та Symfony — два найпопулярніші PHP-фреймворки. Вони мають схожі концепції, але різну організацію коду.
## Таблиця порівняння

| Концепція | Laravel | Symfony |
|-----------|---------|---------|
| **Контролери** | `app/Http/Controllers/` | `src/Controller/` |
| **Моделі** | `app/Models/` (Eloquent ORM) | `src/Entity/` (Doctrine ORM) |
| **Маршрути** | `routes/web.php`, `routes/api.php` | `config/routes.yaml` або атрибути в контролерах |
| **Конфігурація середовища** | `.env` | `.env` або `.env.local` |
| **Консольні команди** | `php artisan` | `bin/console` |
| **Міграції** | `database/migrations/` | `migrations/` (Doctrine Migrations) |
| **Шаблони** | `resources/views/` (Blade) | `templates/` (Twig) |
| **Middleware** | `app/Http/Middleware/` | `src/Middleware/` |
| **Сервіс-провайдери** | `app/Providers/` | `src/Kernel.php` (Dependency Injection) |
| **Тести** | `tests/` (PHPUnit) | `tests/` (PHPUnit) |
| **Точка входу** | `public/index.php` | `public/index.php` |

---

## Основні схожості

1. **Єдина точка входу** — обидва фреймворки використовують `public/index.php` як front controller

2. **Конфігурація через .env** — обидва використовують змінні середовища для налаштувань

3. **Маршрутизація** — обидва мають централізоване визначення маршрутів

4. **MVC архітектура** — обидва дотримуються патерну Model-View-Controller

5. **Бізнес-логіка** — в обох фреймворках логіка виноситься з роутів у контролери та сервіси

6. **ORM** — обидва мають вбудовані ORM (Eloquent у Laravel, Doctrine у Symfony)

7. **Консольні інструменти** — обидва мають потужні CLI-інструменти (Artisan у Laravel, Console у Symfony)

---

## Основні відмінності

| Аспект | Laravel | Symfony |
|--------|---------|---------|
| **ORM** | Eloquent (Active Record) | Doctrine (Data Mapper) |
| **Шаблонізатор** | Blade | Twig |
| **Конфігурація** | Менше конфігурації, багато "магії" | Більше явної конфігурації |
| **Підхід** | "Конвенція над конфігурацією" | "Гнучкість та конфігурація" |
| **Навчання** | Простіший поріг входу | Складніший, але більш гнучкий |
| **Компоненти** | Монолітний фреймворк | Модульна архітектура (можна використовувати окремі компоненти) |

---

## Приклад: Маршрутизація

### Laravel (`routes/web.php`)

```php
use Modules\Client\src\Interfaces\Http\Api\V1\ClientController;Route::get('/clients', [ClientController::class, 'index']);
Route::get('/clients/{id}', [ClientController::class, 'show']);
```

### Symfony (`config/routes.yaml`)
```yaml
app_clients:
    path: /clients
    controller: App\Controller\ClientController::index

app_client_show:
    path: /clients/{id}
    controller: App\Controller\ClientController::show
```

Або з атрибутами в контролері:
```php
#[Route('/clients', name: 'app_clients')]
public function index(): Response
{
    // ...
}
```

---

## Приклад: Консольні команди

### Laravel
```bash
php artisan make:controller ClientController
php artisan make:model Client
php artisan migrate
php artisan serve
```

### Symfony
```bash
bin/console make:controller ClientController
bin/console make:entity Client
bin/console doctrine:migrations:migrate
bin/console server:run
```

---

## Критерiї вибору?

### Laravel підходить для:
- Швидкої розробки
- Простих та середніх проектів
- Команд, які цінують простоту та швидкість
- Проектів, де важлива "конвенція над конфігурацією"

### Symfony підходить для:
- Великих корпоративних проектів
- Проектів, де потрібна максимальна гнучкість
- Команд, які цінують явну конфігурацію
- Проектів, де планується використовувати окремі компоненти

---

## Висновок

Обидва фреймворки — Laravel та Symfony — є потужними інструментами для розробки веб-додатків на PHP. Вони мають схожу архітектуру (MVC, єдина точка входу, конфігурація через .env), але різний підхід до організації коду. Laravel надає більше "магії" та конвенцій, що прискорює розробку, тоді як Symfony пропонує більше гнучкості та явної конфігурації, що важливо для великих проектів. Вибір між ними залежить від потреб проекту та досвіду команди.
