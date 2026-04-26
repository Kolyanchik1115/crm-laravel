# API Contract CRM

## 📑 Зміст

1. [Версіонування](#версіонування)
2. [Формат помилок](#формат-помилок)
3. [Ресурс vs дія: Антипатерни та правильний підхід](#ресурс-vs-дія-антипатерни-та-правильний-підхід)
    - [Правило](#правило)
    - [Таблиця порівняння](#таблиця-порівняння-погано-vs-добре)
    - [Альтернативний підхід](#альтернативний-підхід-винесення-балансу-в-окремий-підресурс)
    - [Чому дії в URL — це антипатерн?](#чому-дії-в-url-це-антипатерн)
    - [Як перевірити API на правильність?](#як-перевірити-api-на-правильність)
    - [Правильні приклади для CRM](#правильні-приклади-для-crm)
4. [Ресурс: Transfers (Перекази)](#ресурс-transfers-перекази)
    - [POST /api/v1/transfers](#post-apiv1transfers)
    - [GET /api/v1/transfers](#get-apiv1transfers)
    - [GET /api/v1/transfers/{id}](#get-apiv1transfersid)
5. [Ресурс: Accounts (Рахунки)](#ресурс-accounts-рахунки)
    - [GET /api/v1/accounts](#get-apiv1accounts)
    - [GET /api/v1/accounts/{id}](#get-apiv1accountsid)
    - [GET /api/v1/accounts/{id}/transactions](#get-apiv1accountsidtransactions)
6. [Ресурс: Invoices (Рахунки-фактури)](#ресурс-invoices-рахунки-фактури)
    - [POST /api/v1/invoices](#post-apiv1invoices)
    - [GET /api/v1/invoices](#get-apiv1invoices)
    - [GET /api/v1/invoices/{id}](#get-apiv1invoicesid)
    - [PATCH /api/v1/invoices/{id}](#patch-apiv1invoicesid)
7. [Діаграма ресурсів](#діаграма-ресурсів)
8. [Підсумкова таблиця контракту API](#підсумкова-таблиця-контракту-API)
9. [Чек-лист для transfers](#чеклист-для-transfers)
10. [Валідація vs бізнес-логіка](#валідація-vs-бізнес-логіка)
11. [Документацiя API](#документація-api)
12. [Перевірка відповідності між OpenAPI специфікацією та тестами](#API-Contract-Validation-Checklist)

---

## Версіонування

### Поточна версія: v1

Всі API ендпоінти використовують префікс `/api/v1/` для підтримки зворотної сумісності.

### Правила змін у межах v1 (стабільний контракт)

Поки клієнт (банк, платіжна система, бухгалтерія) використовує `v1`:
- Формат відповіді та поведінка **не змінюються несумісно**

#### ✅ Дозволено в рамках v1:
- Додавання нових **опціональних** полів у запит або відповідь
- Додавання нових ендпоінтів
- Розширення списку допустимих значень у enum (якщо клієнт ігнорує невідомі значення)

#### ❌ Заборонено без створення v2:
- Видалення полів
- Перейменування полів
- Зміна типу поля
- Зміна семантики статус-кодів
- Зміна обов'язковості поля (опціональне → обов'язкове)

### Приклад breaking change для майбутньої версії v2

**Що зміниться (гіпотетично):**

| v1 | v2 | Тип зміни |
|----|----|-----------|
| `account_from_id` | `source_account_id` | Перейменування (breaking) |
| `account_to_id` | `target_account_id` | Перейменування (breaking) |
| (немає) | `commission_amount` | Нове опціональне поле (не breaking) |

**Приклад тіла запиту v2:**
```
json {
    "source_account_id": 1,
    "target_account_id": 2,
    "amount": "1500.00",
    "commission_amount": "15.00"
}
```
---

### Структура для майбутньої v2

```
App/Http/Controllers/Api/
├── V1/
│   ├── TransferController.php
│   └── InvoiceController.php
└── V2/                    (додається при виході v2)
    ├── TransferController.php
    └── InvoiceController.php
```

```php
// routes/api.php
Route::prefix('v1')->group(function () {
    Route::apiResource('transfers', V1\TransferController::class);
});

Route::prefix('v2')->group(function () {   // майбутнє
    Route::apiResource('transfers', V2\TransferController::class);
});
```

### Життєвий цикл версій

| Версія | Статус | Підтримка |
|--------|--------|-----------|
| v1 | Активна | Повна (без breaking changes) |
| v2 | Планова | Розробка, breaking changes дозволені |

> **Для фінансових інтеграцій:** Перехід на v2 — добровільний. v1 підтримується мінімум 6 місяців після анонсу v2.

---

## Завдання 4. Централізована обробка domain-виключень

Ось повна реалізація:

---

## 1. Оновлення `App\Exceptions\Handler.php`

```php
<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // Перевіряємо, чи це API запит
        if ($request->expectsJson() || $request->is('api/*')) {
            
            // 1. Бізнес-винятки (422)
            if ($e instanceof InsufficientBalanceException) {
                return response()->json([
                    'message' => 'Недостатньо коштів на рахунку.',
                    'code' => 'INSUFFICIENT_BALANCE',
                ], 422);
            }
            
            if ($e instanceof SameAccountTransferException) {
                return response()->json([
                    'message' => 'Рахунок відправника і одержувача не можуть збігатися.',
                    'code' => 'SAME_ACCOUNT_TRANSFER',
                ], 422);
            }
            
            // 2. DomainException (422)
            if ($e instanceof \DomainException) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'code' => 'DOMAIN_ERROR',
                ], 422);
            }
            
            // 3. Model not found (404)
            if ($e instanceof ModelNotFoundException) {
                return response()->json([
                    'message' => 'Ресурс не знайдено.',
                ], 404);
            }
            
            // 4. Validation errors (422) - залишаємо стандартну обробку Laravel
            if ($e instanceof ValidationException) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422);
            }
            
            // 5. Системні помилки (500) - не показуємо stack trace
            if ($this->isHttpException($e)) {
                $statusCode = $e->getStatusCode();
                
                // 404 Not Found для неіснуючих маршрутів
                if ($statusCode === 404) {
                    return response()->json([
                        'message' => 'Ресурс не знайдено.',
                    ], 404);
                }
            }
            
            // Всі інші помилки - 500
            return response()->json([
                'message' => 'Внутрішня помилка сервера.',
            ], 500);
        }
        
        return parent::render($request, $e);
    }
}
```

---

## 2. Створення класів винятків (якщо ще не створені)

### `App\Exceptions\InsufficientBalanceException.php`

```php
<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class InsufficientBalanceException extends Exception
{
    public function __construct(string $message = "Недостатньо коштів на рахунку")
    {
        parent::__construct($message);
    }
}
```

### `App\Exceptions\SameAccountTransferException.php`

```php
<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class SameAccountTransferException extends Exception
{
    public function __construct(string $message = "Рахунок відправника і одержувача не можуть збігатися")
    {
        parent::__construct($message);
    }
}
```

---

## 3. Спрощення контролера (видаляємо try-catch)

Тепер `TransferController` стає чистішим:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTransferRequest;
use App\Http\Resources\Api\V1\TransferResource;
use App\Models\Transaction;
use App\Services\TransferService;
use Illuminate\Http\JsonResponse;

class TransferController extends Controller
{
    protected TransferService $transferService;

    public function __construct(TransferService $transferService)
    {
        $this->transferService = $transferService;
    }

    public function index(): JsonResponse
    {
        $perPage = (int) request()->get('per_page', 15);
        
        $transfers = Transaction::with(['account'])
            ->where('type', 'transfer_out')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return TransferResource::collection($transfers)
            ->response()
            ->setStatusCode(200);
    }

    public function show(int $id): JsonResponse
    {
        $transfer = Transaction::with(['account'])
            ->where('type', 'transfer_out')
            ->findOrFail($id);

        return (new TransferResource($transfer))
            ->response()
            ->setStatusCode(200);
    }

    public function store(StoreTransferRequest $request): JsonResponse
    {
        $dto = $request->toTransferDTO();
        $result = $this->transferService->executeTransfer($dto);

        return (new TransferResource((object) $result))
            ->additional([
                'success' => true,
                'message' => 'Переказ успішно виконано',
            ])
            ->response()
            ->setStatusCode(201);
    }
}
```

## Формат помилок

API повертає помилки в єдиному JSON-форматі. Статус-коди відповідають типу помилки.

### Валідація (422 Unprocessable Entity)

Помилки валідації FormRequest:

```json
{
    "message": "Сума має бути додатнім числом з максимум двома знаками після коми",
    "errors": {
        "amount": [
            "Сума має бути додатнім числом з максимум двома знаками після коми"
        ]
    }
}
```

### Бізнес-помилки (422 Unprocessable Entity)

Помилки, пов'язані з бізнес-логікою:

| Код помилки | Опис |
|-------------|------|
| `INSUFFICIENT_BALANCE` | Недостатньо коштів на рахунку |
| `SAME_ACCOUNT_TRANSFER` | Рахунок відправника і одержувача збігаються |
| `DOMAIN_ERROR` | Загальна бізнес-помилка |

**Приклад відповіді:**
```json
{
    "message": "Недостатньо коштів на рахунку.",
    "code": "INSUFFICIENT_BALANCE"
}
```

### Ресурс не знайдено (404 Not Found)

```json
{
    "message": "Ресурс не знайдено."
}
```

### Внутрішня помилка сервера (500 Internal Server Error)

```json
{
    "message": "Внутрішня помилка сервера."
}
```

> **Примітка:** Stack trace ніколи не показується клієнту в production-середовищі.
---
### Одна сутність (GET /resource/{id}, POST /resource)

```json
{
    "data": {
        "id": 1,
        "field1": "value1",
        "field2": "value2"
    },
    "success": true,
    "message": "Success message" 
}
```

### Список з пагінацією (GET /resource)

```json
{
    "data": [
        { "id": 1, "field1": "value1" },
        { "id": 2, "field1": "value2" }
    ],
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 10,
        "per_page": 15,
        "to": 15,
        "total": 150
    },
    "links": {
        "first": "http://api.example.com/resource?page=1",
        "last": "http://api.example.com/resource?page=10",
        "prev": null,
        "next": "http://api.example.com/resource?page=2"
    }
}
```

### POST запити (201 Created)

- Повертають створений ресурс в полі `data`
- Додають заголовок `Location: /api/v1/resource/{id}`
- Мають поле `success: true`
- Мають поле `message` з описом операції

**Приклад відповіді:**
```json
{
    "data": {
        "id": 100,
        "account_from_id": 1,
        "amount": "100.00"
    },
    "success": true,
    "message": "Переказ успішно виконано"
}
```

### PUT/PATCH запити (200 OK)

- Повертають оновлений ресурс в полі `data`
- Мають поле `success: true`
- Мають поле `message` (опціонально)

### DELETE запити (204 No Content)

- Не повертають тіло відповіді
- Мають заголовок `Content-Length: 0`
---

## Ресурс vs дія: Антипатерни та правильний підхід

### Правило

> **Дія визначається HTTP-методом; URL лише ідентифікує ресурс.**

| HTTP метод | Дія |
|------------|-----|
| `GET` | Отримання ресурсу(ів) |
| `POST` | Створення нового ресурсу |
| `PUT` | Повне оновлення ресурсу |
| `PATCH` | Часткове оновлення ресурсу |
| `DELETE` | Видалення ресурсу |

### Таблиця порівняння: Погано vs Добре

| ❌ Погано (дія в URL) | ✅ Добре (ресурс + метод) |
|----------------------|--------------------------|
| `POST /createTransfer` | `POST /api/v1/transfers` |
| `GET /getAccountBalance` | `GET /api/v1/accounts/{id}` (баланс у тілі) |
| `POST /cancelInvoice` | `PATCH /api/v1/invoices/{id}` з `{"status": "cancelled"}` |
| `GET /listAllTransfers` | `GET /api/v1/transfers` |
| `POST /updateTransfer` | ❌ Заборонено (фінансова історія незмінна) |
| `DELETE /deleteTransfer` | ❌ Заборонено (фінансова історія незмінна) |
| `GET /getTransferById?id=123` | `GET /api/v1/transfers/123` |
| `POST /markInvoiceAsPaid` | `PATCH /api/v1/invoices/{id}` з `{"status": "paid"}` |
| `GET /getClientAccounts?clientId=1` | `GET /api/v1/accounts?client_id=1` |
| `POST /makeTransfer` | `POST /api/v1/transfers` |

### Альтернативний підхід: винесення балансу в окремий підресурс

Якщо потрібно отримувати тільки баланс (без інших даних рахунку):

| Підхід | URL |
|--------|-----|
| Підресурс | `GET /api/v1/accounts/{id}/balance` |

**Приклад відповіді:**

```json
{
    "account_id": 1,
    "balance": 15000.00,
    "currency": "UAH"
}
---

## Ресурс vs дія: Антипатерни та правильний підхід

### Правило

> **Дія визначається HTTP-методом; URL лише ідентифікує ресурс.**

| HTTP метод | Дія |
|------------|-----|
| `GET` | Отримання ресурсу(ів) |
| `POST` | Створення нового ресурсу |
| `PUT` | Повне оновлення ресурсу |
| `PATCH` | Часткове оновлення ресурсу |
| `DELETE` | Видалення ресурсу |

### Таблиця порівняння: Погано vs Добре

| ❌ Погано (дія в URL) | ✅ Добре (ресурс + метод) |
|----------------------|--------------------------|
| `POST /createTransfer` | `POST /api/v1/transfers` |
| `GET /getAccountBalance` | `GET /api/v1/accounts/{id}` (баланс у тілі) |
| `POST /cancelInvoice` | `PATCH /api/v1/invoices/{id}` з `{"status": "cancelled"}` |
| `GET /listAllTransfers` | `GET /api/v1/transfers` |
| `POST /updateTransfer` | ❌ Заборонено (фінансова історія незмінна) |
| `DELETE /deleteTransfer` | ❌ Заборонено (фінансова історія незмінна) |
| `GET /getTransferById?id=123` | `GET /api/v1/transfers/123` |
| `POST /markInvoiceAsPaid` | `PATCH /api/v1/invoices/{id}` з `{"status": "paid"}` |
| `GET /getClientAccounts?clientId=1` | `GET /api/v1/accounts?client_id=1` |
| `POST /makeTransfer` | `POST /api/v1/transfers` |

### Альтернативний підхід: винесення балансу в окремий підресурс

Якщо потрібно отримувати тільки баланс (без інших даних рахунку):

| Підхід | URL |
|--------|-----|
| Підресурс | `GET /api/v1/accounts/{id}/balance` |

**Приклад відповіді:**

```json
{
    "account_id": 1,
    "balance": 15000.00,
    "currency": "UAH"
}
```

### Чому дії в URL — це антипатерн?

| Проблема | Пояснення |
|----------|-----------|
| **Не RESTful** | Порушує принципи REST, де URL позначає ресурс, а методи — дії |
| **Важко документувати** | Кожна дія — унікальний URL, немає єдиного шаблону |
| **Нестандартні методи** | Немає чіткої відповідності між HTTP методом і дією |
| **Ускладнює кешування** | Кеш працює з HTTP методами (GET кешується, POST — ні) |
| **Дублювання коду** | Схожі дії призводять до дублювання логіки |

### Як перевірити API на правильність?

1. **Подивись на URL** — чи є в ньому дієслово? (`create`, `update`, `delete`, `get`, `list`)
2. **Перевір HTTP метод** — чи відповідає він дії?
    - Читання → `GET`
    - Створення → `POST`
    - Оновлення → `PUT` / `PATCH`
    - Видалення → `DELETE`
3. **Чи можна передбачити URL?** — якщо знаєш ID ресурсу, чи зможеш сформувати URL?

### Правильні приклади для CRM

| Ресурс | GET (читання) | POST (створення) | PATCH (оновлення) | DELETE (видалення) |
|--------|---------------|------------------|-------------------|-------------------|
| `transfers` | `/api/v1/transfers` `/api/v1/transfers/{id}` | `/api/v1/transfers` | ❌ заборонено | ❌ заборонено |
| `accounts` | `/api/v1/accounts` `/api/v1/accounts/{id}` | ❌ (через клієнта) | ❌ заборонено | ❌ заборонено |
| `invoices` | `/api/v1/invoices` `/api/v1/invoices/{id}` | `/api/v1/invoices` | `/api/v1/invoices/{id}` (тільки статус) | ❌ заборонено |
| `clients` | `/api/v1/clients` `/api/v1/clients/{id}` | `/api/v1/clients` | `/api/v1/clients/{id}` | ❌ заборонено |

### Висновок

- ✅ **Добре**: `GET /api/v1/transfers/123`
- ❌ **Погано**: `POST /getTransferById?id=123`
- ✅ **Добре**: `PATCH /api/v1/invoices/123` з `{"status": "paid"}`
- ❌ **Погано**: `POST /markInvoiceAsPaid?id=123`

> **Запам'ятай:** URL — це іменник (ресурс). HTTP метод — це дієслово (дія).

---

## Ресурс: Transfers (Перекази)

### Опис

Ресурс `transfers` представляє операції переказу коштів між рахунками.
Це критична фінансова операція, тому видалення та оновлення заборонені.

### Ендпоінти

| HTTP метод | URL | Призначення |
|------------|-----|-------------|
| `GET` | `/api/v1/transfers` | Отримання списку переказів (з фільтрацією) |
| `GET` | `/api/v1/transfers/{id}` | Отримання деталей одного переказу за ID |
| `POST` | `/api/v1/transfers` | Створення нового переказу |

### Принципи проектування

- ✅ Використання іменника у множині: `transfers`
- ✅ Відсутність дієслів у URL (немає `/createTransfer`, `/doTransfer`)
- ✅ Версіонування через префікс `/v1/`
- ✅ RESTful підхід: POST для створення, GET для читання
- ❌ Немає PUT/PATCH/DELETE (фінансова історія незмінна)

---

## POST /api/v1/transfers

[↑ Зміст](#-зміст)

### Опис
Створює новий переказ коштів між двома рахунками.

### HTTP метод
`POST`

### URL
`/api/v1/transfers`

### Заголовки

| Header | Значення | Обов'язковість |
|--------|----------|----------------|
| `Content-Type` | `application/json` | Обов'язковий |
| `Accept` | `application/json` | Опціональний |

### Тіло запиту (Request Body)

```json
{
    "account_from_id": 1,
    "account_to_id": 2,
    "amount": "1500.00",
    "currency": "UAH",
    "description": "Оплата за послуги"
}
```

### Параметри

| Поле | Тип | Обов'язковість | Опис | Валідація |
|------|-----|----------------|------|-----------|
| `account_from_id` | integer | Так | ID рахунку відправника | Існує в БД |
| `account_to_id` | integer | Так | ID рахунку отримувача | Існує в БД, відрізняється від `account_from_id` |
| `amount` | string | Так | Сума переказу | > 0, формат "0.00" |
| `currency` | string | Ні | Валюта (ISO 4217) | За замовчуванням "UAH" |
| `description` | string | Ні | Опис переказу | Максимум 500 символів |

### Приклад запиту (cURL)

```bash
curl -X POST http://localhost:8000/api/v1/transfers \
  -H "Content-Type: application/json" \
  -d '{
    "account_from_id": 1,
    "account_to_id": 2,
    "amount": "1500.00",
    "currency": "UAH",
    "description": "Оплата за послуги"
  }'
```

### Успішна відповідь (201 Created)

```json
{
    "success": true,
    "message": "Переказ успішно виконано",
    "data": {
        "transaction_out_id": 100,
        "transaction_in_id": 101,
        "amount": 1500.00,
        "commission": 0,
        "created_at": "2026-04-20T10:30:00+00:00"
    }
}
```

### Помилка валідації (422 Unprocessable Entity)

```json
{
    "success": false,
    "message": "Рахунки мають бути різними",
    "errors": {
        "to_account_id": [
            "Рахунки мають бути різними"
        ]
    }
}
```

### Помилка недостатньо коштів (422 Unprocessable Entity)

```json
{
    "success": false,
    "message": "Insufficient funds for transfer"
}
```

### Помилка рахунок не знайдено (404 Not Found)

```json
{
    "success": false,
    "message": "Account not found"
}
```

### Коди відповідей

| Код | Опис |
|-----|------|
| 201 | Created - переказ успішно створено |
| 400 | Bad Request - невірний формат запиту |
| 404 | Not Found - рахунок не знайдено |
| 422 | Unprocessable Entity - помилка валідації або недостатньо коштів |
| 500 | Internal Server Error - внутрішня помилка сервера |

---

## GET /api/v1/transfers

[↑ Зміст](#-зміст)

### Опис
Отримує список переказів з можливістю фільтрації.

### HTTP метод
`GET`

### URL
`/api/v1/transfers`

### Параметри запиту (Query Parameters)

| Параметр | Тип | Обов'язковість | Опис |
|----------|-----|----------------|------|
| `account_id` | integer | Ні | Фільтр за ID рахунку (відправник або отримувач) |
| `date_from` | string (ISO 8601) | Ні | Фільтр за датою "від" |
| `date_to` | string (ISO 8601) | Ні | Фільтр за датою "до" |
| `per_page` | integer | Ні | Кількість записів на сторінці (за замовчуванням 15) |
| `page` | integer | Ні | Номер сторінки (за замовчуванням 1) |

### Приклад запиту (cURL)

```bash
curl "http://localhost:8000/api/v1/transfers?account_id=1&date_from=2026-01-01&per_page=10"
```

### Успішна відповідь (200 OK)

```json
{
    "data": [
        {
            "id": 100,
            "transaction_out_id": 100,
            "transaction_in_id": 101,
            "from_account_id": 1,
            "to_account_id": 2,
            "amount": 1500.00,
            "commission": 0,
            "currency": "UAH",
            "description": "Оплата за послуги",
            "status": "completed",
            "created_at": "2026-04-20T10:30:00+00:00"
        }
    ],
    "links": {
        "first": "http://localhost:8000/api/v1/transfers?page=1",
        "last": "http://localhost:8000/api/v1/transfers?page=5",
        "prev": null,
        "next": "http://localhost:8000/api/v1/transfers?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 5,
        "per_page": 15,
        "to": 15,
        "total": 72
    }
}
```

### Коди відповідей

| Код | Опис |
|-----|------|
| 200 | OK - успішне отримання списку |
| 422 | Unprocessable Entity - невірні параметри фільтрації |
| 500 | Internal Server Error - внутрішня помилка сервера |

---

## GET /api/v1/transfers/{id}

[↑ Зміст](#-зміст)

### Опис
Отримує детальну інформацію про один переказ за ID.

### HTTP метод
`GET`

### URL
`/api/v1/transfers/{id}`

### Параметри шляху (Path Parameters)

| Параметр | Тип | Обов'язковість | Опис |
|----------|-----|----------------|------|
| `id` | integer | Так | ID переказу (transaction_out_id) |

### Приклад запиту (cURL)

```bash
curl http://localhost:8000/api/v1/transfers/100
```

### Успішна відповідь (200 OK)

```json
{
    "data": {
        "id": 100,
        "transaction_out_id": 100,
        "transaction_in_id": 101,
        "from_account_id": 1,
        "to_account_id": 2,
        "from_account_number": "UA1234567890",
        "to_account_number": "UA0987654321",
        "amount": 1500.00,
        "commission": 0,
        "currency": "UAH",
        "description": "Оплата за послуги",
        "status": "completed",
        "from_client_name": "Іван Петренко",
        "to_client_name": "Марія Шевченко",
        "created_at": "2026-04-20T10:30:00+00:00"
    }
}
```

### Помилка (404 Not Found)

```json
{
    "success": false,
    "message": "Transfer not found"
}
```

### Коди відповідей

| Код | Опис |
|-----|------|
| 200 | OK - успішне отримання переказу |
| 404 | Not Found - переказ не знайдено |
| 500 | Internal Server Error - внутрішня помилка сервера |

---

## Ресурс: Accounts (Рахунки)

[↑ Зміст](#-зміст)

### Опис

Ресурс `accounts` представляє банківські рахунки клієнтів. Кожен рахунок належить одному клієнту та має унікальний номер, баланс і валюту.

### Ендпоінти

| HTTP метод | URL | Призначення |
|------------|-----|-------------|
| `GET` | `/api/v1/accounts` | Отримання списку рахунків (з фільтрацією) |
| `GET` | `/api/v1/accounts/{id}` | Отримання деталей одного рахунку |
| `GET` | `/api/v1/accounts/{id}/transactions` | Отримання транзакцій рахунку (підресурс) |

### Принципи проектування

- ✅ Використання іменника у множині: `accounts`
- ✅ Вкладені ресурси: `/accounts/{id}/transactions`
- ✅ Версіонування через префікс `/v1/`
- ❌ Немає POST/PUT/PATCH/DELETE для рахунків (зміна балансу тільки через перекази)

---

## GET /api/v1/accounts

[↑ Зміст](#-зміст)

### Опис
Отримує список рахунків з можливістю фільтрації за клієнтом.

### HTTP метод
`GET`

### URL
`/api/v1/accounts`

### Параметри запиту (Query Parameters)

| Параметр | Тип | Обов'язковість | Опис |
|----------|-----|----------------|------|
| `client_id` | integer | Ні | Фільтр за ID клієнта |
| `currency` | string | Ні | Фільтр за валютою (UAH, USD, EUR) |
| `per_page` | integer | Ні | Кількість записів на сторінці (за замовчуванням 15) |
| `page` | integer | Ні | Номер сторінки (за замовчуванням 1) |

### Приклад запиту (cURL)

```bash
curl "http://localhost:8000/api/v1/accounts?client_id=1&per_page=10"
```

### Успішна відповідь (200 OK)

```json
{
    "data": [
        {
            "id": 1,
            "account_number": "UA1234567890",
            "balance": 15000.00,
            "currency": "UAH",
            "client_id": 1,
            "client_name": "Іван Петренко",
            "created_at": "2026-01-15T10:00:00+00:00"
        },
        {
            "id": 2,
            "account_number": "UA0987654321",
            "balance": 5000.00,
            "currency": "USD",
            "client_id": 1,
            "client_name": "Іван Петренко",
            "created_at": "2026-01-15T10:00:00+00:00"
        }
    ],
    "links": {
        "first": "http://localhost:8000/api/v1/accounts?page=1",
        "last": "http://localhost:8000/api/v1/accounts?page=3",
        "prev": null,
        "next": "http://localhost:8000/api/v1/accounts?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 3,
        "per_page": 15,
        "to": 15,
        "total": 45
    }
}
```

### Коди відповідей

| Код | Опис |
|-----|------|
| 200 | OK - успішне отримання списку |
| 422 | Unprocessable Entity - невірні параметри фільтрації |
| 500 | Internal Server Error - внутрішня помилка сервера |

---

## GET /api/v1/accounts/{id}

[↑ Зміст](#-зміст)

### Опис
Отримує детальну інформацію про один рахунок за ID.

### HTTP метод
`GET`

### URL
`/api/v1/accounts/{id}`

### Параметри шляху (Path Parameters)

| Параметр | Тип | Обов'язковість | Опис |
|----------|-----|----------------|------|
| `id` | integer | Так | ID рахунку |

### Приклад запиту (cURL)

```bash
curl http://localhost:8000/api/v1/accounts/1
```

### Успішна відповідь (200 OK)

```json
{
    "data": {
        "id": 1,
        "account_number": "UA1234567890",
        "balance": 15000.00,
        "currency": "UAH",
        "client_id": 1,
        "client_name": "Іван Петренко",
        "client_email": "ivan@example.com",
        "created_at": "2026-01-15T10:00:00+00:00",
        "updated_at": "2026-04-20T10:30:00+00:00"
    }
}
```

### Помилка (404 Not Found)

```json
{
    "success": false,
    "message": "Account not found"
}
```

### Коди відповідей

| Код | Опис |
|-----|------|
| 200 | OK - успішне отримання рахунку |
| 404 | Not Found - рахунок не знайдено |
| 500 | Internal Server Error - внутрішня помилка сервера |

---

## GET /api/v1/accounts/{id}/transactions

[↑ Зміст](#-зміст)

### Опис
Отримує список транзакцій для конкретного рахунку (підресурс).

### HTTP метод
`GET`

### URL
`/api/v1/accounts/{id}/transactions`

### Параметри шляху (Path Parameters)

| Параметр | Тип | Обов'язковість | Опис |
|----------|-----|----------------|------|
| `id` | integer | Так | ID рахунку |

### Параметри запиту (Query Parameters)

| Параметр | Тип | Обов'язковість | Опис |
|----------|-----|----------------|------|
| `type` | string | Ні | Фільтр за типом (deposit, withdrawal, transfer_in, transfer_out, fee) |
| `date_from` | string | Ні | Фільтр за датою "від" (ISO 8601) |
| `date_to` | string | Ні | Фільтр за датою "до" (ISO 8601) |
| `per_page` | integer | Ні | Кількість записів на сторінці (за замовчуванням 15) |
| `page` | integer | Ні | Номер сторінки (за замовчуванням 1) |

### Приклад запиту (cURL)

```bash
curl "http://localhost:8000/api/v1/accounts/1/transactions?type=transfer_out&per_page=10"
```

### Успішна відповідь (200 OK)

```json
{
    "data": [
        {
            "id": 100,
            "amount": -1500.00,
            "type": "transfer_out",
            "status": "completed",
            "description": "Переказ на рахунок UA0987654321",
            "created_at": "2026-04-20T10:30:00+00:00"
        },
        {
            "id": 95,
            "amount": 500.00,
            "type": "deposit",
            "status": "completed",
            "description": "Поповнення рахунку",
            "created_at": "2026-04-15T14:20:00+00:00"
        }
    ],
    "links": {
        "first": "http://localhost:8000/api/v1/accounts/1/transactions?page=1",
        "last": "http://localhost:8000/api/v1/accounts/1/transactions?page=5",
        "prev": null,
        "next": "http://localhost:8000/api/v1/accounts/1/transactions?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 5,
        "per_page": 15,
        "to": 15,
        "total": 72
    }
}
```

### Коди відповідей

| Код | Опис |
|-----|------|
| 200 | OK - успішне отримання транзакцій |
| 404 | Not Found - рахунок не знайдено |
| 422 | Unprocessable Entity - невірні параметри фільтрації |
| 500 | Internal Server Error - внутрішня помилка сервера |

---

## Ресурс: Invoices (Рахунки-фактури)

[↑ Зміст](#-зміст)

### Опис

Ресурс `invoices` представляє рахунки-фактури для клієнтів. Кожен рахунок містить позиції з послугами, кількістю та ціною.

### Ендпоінти

| HTTP метод | URL | Призначення |
|------------|-----|-------------|
| `GET` | `/api/v1/invoices` | Отримання списку рахунків-фактур |
| `GET` | `/api/v1/invoices/{id}` | Отримання деталей одного рахунку-фактури з позиціями |
| `POST` | `/api/v1/invoices` | Створення нового рахунку-фактури |
| `PATCH` | `/api/v1/invoices/{id}` | Оновлення статусу рахунку-фактури (опціонально) |

### Принципи проектування

- ✅ Використання іменника у множині: `invoices`
- ✅ Версіонування через префікс `/v1/`
- ✅ POST для створення, GET для читання
- ⚠️ PATCH тільки для статусу (не для повного оновлення)

---

## POST /api/v1/invoices

[↑ Зміст](#-зміст)

### Опис
Створює новий рахунок-фактуру для клієнта з позиціями послуг.

### HTTP метод
`POST`

### URL
`/api/v1/invoices`

### Тіло запиту (Request Body)

```json
{
    "client_id": 1,
    "currency": "UAH",
    "items": [
        {
            "service_id": 1,
            "quantity": 2,
            "unit_price": "500.00"
        },
        {
            "service_id": 2,
            "quantity": 1,
            "unit_price": "1200.00"
        }
    ]
}
```

### Параметри

| Поле | Тип | Обов'язковість | Опис | Валідація |
|------|-----|----------------|------|-----------|
| `client_id` | integer | Так | ID клієнта | Існує в БД |
| `currency` | string | Ні | Валюта (ISO 4217) | За замовчуванням "UAH" |
| `items` | array | Так | Масив позицій рахунку | Мінімум 1 позиція |
| `items[].service_id` | integer | Так | ID послуги | Існує в БД |
| `items[].quantity` | integer | Так | Кількість | ≥ 1 |
| `items[].unit_price` | string | Так | Ціна за одиницю | > 0, формат "0.00" |

### Приклад запиту (cURL)

```bash
curl -X POST http://localhost:8000/api/v1/invoices \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": 1,
    "currency": "UAH",
    "items": [
        {"service_id": 1, "quantity": 2, "unit_price": "500.00"},
        {"service_id": 2, "quantity": 1, "unit_price": "1200.00"}
    ]
  }'
```

### Успішна відповідь (201 Created)

```json
{
    "success": true,
    "message": "Рахунок-фактуру створено",
    "data": {
        "id": 50,
        "invoice_number": "INV-20260420-0001",
        "total_amount": 2200.00,
        "status": "draft",
        "client_id": 1,
        "created_at": "2026-04-20T10:30:00+00:00"
    }
}
```

### Помилка валідації (422 Unprocessable Entity)

```json
{
    "success": false,
    "message": "Service with ID 999 not found"
}
```

### Коди відповідей

| Код | Опис |
|-----|------|
| 201 | Created - рахунок успішно створено |
| 422 | Unprocessable Entity - помилка валідації |
| 500 | Internal Server Error - внутрішня помилка сервера |

---

## GET /api/v1/invoices

[↑ Зміст](#-зміст)

### Опис
Отримує список рахунків-фактур.

### HTTP метод
`GET`

### URL
`/api/v1/invoices`

### Параметри запиту (Query Parameters)

| Параметр | Тип | Обов'язковість | Опис |
|----------|-----|----------------|------|
| `client_id` | integer | Ні | Фільтр за ID клієнта |
| `status` | string | Ні | Фільтр за статусом (draft, sent, paid, overdue, cancelled) |
| `date_from` | string | Ні | Фільтр за датою "від" (ISO 8601) |
| `date_to` | string | Ні | Фільтр за датою "до" (ISO 8601) |
| `per_page` | integer | Ні | Кількість записів на сторінці (за замовчуванням 15) |
| `page` | integer | Ні | Номер сторінки (за замовчуванням 1) |

### Приклад запиту (cURL)

```bash
curl "http://localhost:8000/api/v1/invoices?client_id=1&status=paid"
```

### Успішна відповідь (200 OK)

```json
{
    "data": [
        {
            "id": 50,
            "invoice_number": "INV-20260420-0001",
            "total_amount": 2200.00,
            "status": "draft",
            "client_id": 1,
            "client_name": "Іван Петренко",
            "created_at": "2026-04-20T10:30:00+00:00"
        }
    ],
    "links": {
        "first": "http://localhost:8000/api/v1/invoices?page=1",
        "last": "http://localhost:8000/api/v1/invoices?page=3",
        "prev": null,
        "next": "http://localhost:8000/api/v1/invoices?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 3,
        "per_page": 15,
        "to": 15,
        "total": 45
    }
}
```

### Коди відповідей

| Код | Опис |
|-----|------|
| 200 | OK - успішне отримання списку |
| 422 | Unprocessable Entity - невірні параметри фільтрації |
| 500 | Internal Server Error - внутрішня помилка сервера |

---

## GET /api/v1/invoices/{id}

[↑ Зміст](#-зміст)

### Опис
Отримує детальну інформацію про один рахунок-фактуру з позиціями.

### HTTP метод
`GET`

### URL
`/api/v1/invoices/{id}`

### Параметри шляху (Path Parameters)

| Параметр | Тип | Обов'язковість | Опис |
|----------|-----|----------------|------|
| `id` | integer | Так | ID рахунку-фактури |

### Приклад запиту (cURL)

```bash
curl http://localhost:8000/api/v1/invoices/50
```

### Успішна відповідь (200 OK)

```json
{
    "data": {
        "id": 50,
        "invoice_number": "INV-20260420-0001",
        "total_amount": 2200.00,
        "status": "draft",
        "issued_at": "2026-04-20",
        "client_id": 1,
        "client_name": "Іван Петренко",
        "client_email": "ivan@example.com",
        "items": [
            {
                "service_id": 1,
                "service_name": "Консультація фінансового експерта",
                "quantity": 2,
                "unit_price": 500.00,
                "total": 1000.00
            },
            {
                "service_id": 2,
                "service_name": "Розробка корпоративного сайту",
                "quantity": 1,
                "unit_price": 1200.00,
                "total": 1200.00
            }
        ],
        "created_at": "2026-04-20T10:30:00+00:00",
        "updated_at": "2026-04-20T10:30:00+00:00"
    }
}
```

### Помилка (404 Not Found)

```json
{
    "success": false,
    "message": "Invoice not found"
}
```

### Коди відповідей

| Код | Опис |
|-----|------|
| 200 | OK - успішне отримання рахунку |
| 404 | Not Found - рахунок не знайдено |
| 500 | Internal Server Error - внутрішня помилка сервера |

---

## PATCH /api/v1/invoices/{id}

[↑ Зміст](#-зміст)

### Опис
Оновлює статус рахунку-фактури (наприклад, після оплати).

### HTTP метод
`PATCH`

### URL
`/api/v1/invoices/{id}`

### Тіло запиту (Request Body)

```json
{
    "status": "paid"
}
```

### Параметри

| Поле | Тип | Обов'язковість | Опис |
|------|-----|----------------|------|
| `status` | string | Так | Новий статус (draft, sent, paid, overdue, cancelled) |

### Приклад запиту (cURL)

```bash
curl -X PATCH http://localhost:8000/api/v1/invoices/50 \
  -H "Content-Type": application/json \
  -d '{"status": "paid"}'
```

### Успішна відповідь (200 OK)

```json
{
    "success": true,
    "message": "Статус рахунку оновлено",
    "data": {
        "id": 50,
        "status": "paid",
        "updated_at": "2026-04-20T11:00:00+00:00"
    }
}
```

### Коди відповідей

| Код | Опис |
|-----|------|
| 200 | OK - статус успішно оновлено |
| 404 | Not Found - рахунок не знайдено |
| 422 | Unprocessable Entity - невірний статус |
| 500 | Internal Server Error - внутрішня помилка сервера |

---

## Діаграма ресурсів

[↑ Зміст](#-зміст)

```
/api/v1/
clients
│   ├── GET /                 (список клієнтів)
│   ├── GET /{id}             (деталі клієнта)
│   └── GET /{id}/accounts    (рахунки клієнта) 
│
├── accounts
│   ├── GET /                 (список рахунків)
│   ├── GET /{id}             (деталі рахунку)
│   └── GET /{id}/transactions (транзакції рахунку)
│
├── invoices
│   ├── GET /                 (список рахунків-фактур)
│   ├── GET /{id}             (деталі рахунку-фактури)
│   ├── POST /                (створення рахунку-фактури)
│   └── PATCH /{id}           (оновлення статусу)
│
└── transfers
    ├── GET /                 (список переказів)
    ├── GET /{id}             (деталі переказу)
    └── POST /                (створення переказу)
```
## Підсумкова таблиця контракту API

[↑ Зміст](#-зміст)

### Таблиця всіх ендпоінтів

| Ендпоінт | Метод | Призначення | Тіло запиту (POST/PATCH) | Успіх (2xx) | Помилки (4xx, 5xx) |
|----------|-------|-------------|--------------------------|-------------|--------------------|
| `/api/v1/transfers` | GET | Отримання списку переказів з фільтрацією | — | 200 | 422, 500 |
| `/api/v1/transfers` | POST | Створення нового переказу коштів | `account_from_id`, `account_to_id`, `amount`, `currency`(опц), `description`(опц) | 201 | 400, 404, 422, 500 |
| `/api/v1/transfers/{id}` | GET | Отримання деталей одного переказу | — | 200 | 404, 500 |
| `/api/v1/accounts` | GET | Отримання списку рахунків (з фільтром за `client_id`, `currency`) | — | 200 | 422, 500 |
| `/api/v1/accounts/{id}` | GET | Отримання деталей одного рахунку | — | 200 | 404, 500 |
| `/api/v1/accounts/{id}/transactions` | GET | Отримання транзакцій рахунку (підресурс) | — | 200 | 404, 422, 500 |
| `/api/v1/invoices` | GET | Отримання списку рахунків-фактур | — | 200 | 422, 500 |
| `/api/v1/invoices` | POST | Створення нового рахунку-фактури | `client_id`, `currency`(опц), `items` (масив: `service_id`, `quantity`, `unit_price`) | 201 | 422, 500 |
| `/api/v1/invoices/{id}` | GET | Отримання деталей рахунку-фактури з позиціями | — | 200 | 404, 500 |
| `/api/v1/invoices/{id}` | PATCH | Оновлення статусу рахунку-фактури | `status` (`draft`, `sent`, `paid`, `overdue`, `cancelled`) | 200 | 404, 422, 500 |
| `/api/v1/clients` | GET | Отримання списку клієнтів | — | 200 | 422, 500 |
| `/api/v1/clients/{id}` | GET | Отримання деталей клієнта | — | 200 | 404, 500 |
| `/api/v1/clients/{id}/accounts` | GET | Отримання рахунків клієнта | — | 200 | 404, 500 |

> **Примітка:** Ендпоінти для `clients` згадані у діаграмі ресурсів, але не деталізовані окремо. В таблиці вони включені для повноти контракту.

---

## Резюме: чому обрано саме такі URL та методи

### Прийняті рішення

| Аспект | Рішення | Обґрунтування |
|--------|---------|----------------|
| **Версіонування** | `/api/v1/` у шляху | Фінансові інтеграції не можуть оновлюватися миттєво. Префікс дозволяє паралельно підтримувати v1 та v2 |
| **Іменники vs дієслова** | Тільки іменники (`/transfers`, `/invoices`) | URL ідентифікує ресурс, HTTP метод — дію. Дотримання REST принципів |
| **Множинна форма** | `transfers`, `accounts`, `invoices` | Стандарт REST: GET `/transfers` → список, GET `/transfers/1` → один елемент |
| **Дії через HTTP методи** | `GET` (читання), `POST` (створення), `PATCH` (часткове оновлення) | Використання стандартних методів замість кастомних дій у URL (немає `/markInvoiceAsPaid`) |
| **Вкладені ресурси** | `/accounts/{id}/transactions` | Підресурс логічно належить батьківському. Чітко показує зв'язок "транзакції належать рахунку" |
| **Фінансова незмінність** | Немає `PUT`/`PATCH`/`DELETE` для `transfers` | Перекази — фінансова історія. Їх не можна змінювати або видаляти після створення |
| **Статус замість дії** | `PATCH /invoices/{id}` з `{"status": "paid"}` | Замість `POST /markInvoiceAsPaid` — змінюємо стан ресурсу, а не викликаємо дію |

### Як це спрощує інтеграцію для CRM

1. **Передбачуваність** — розробник інтеграції знає: для роботи з будь-яким ресурсом достатньо вивчити 4-5 стандартних патернів.

2. **Самоописність** — URL `GET /api/v1/accounts/123/transactions` одразу зрозумілий без додаткової документації.

3. **Зворотна сумісність** — версіонування в URL дозволяє оновлювати API без зупинки роботи банків та платіжних систем.

4. **Єдиний стиль помилок** — всі ендпоінти повертають помилки в однаковому форматі:
   ```json
   { "success": false, "message": "...", "errors": {...} }
    ```
5. **Легка генерація OpenAPI** — однорідна структура дозволяє автоматично створювати Swagger/OpenAPI специфікацію.

6. **Фільтрація через query параметри** — замість створення окремих ендпоінтів на /transfers/byAccount/1 — єдиний GET /transfers?account_id=1.

---
## Чеклист для transfers

### Успішний сценарій (201 Created)

| Перевірка | Очікуваний результат |
|-----------|----------------------|
| Статус код | 201 |
| Header Location | `/api/v1/transfers/{id}` |
| Тіло відповіді | `data` з полями: `id`, `account_from_id`, `account_to_id`, `amount`, `currency`, `status`, `description`, `commission`, `created_at` |
| `success` | `true` |
| `message` | "Переказ успішно виконано" |

### Помилки валідації (422)

| Ситуація | Поле | Очікуване повідомлення |
|----------|------|------------------------|
| Сума від'ємна | `amount` | "Сума має бути додатнім числом з максимум двома знаками після коми" |
| Невірна валюта | `currency` | "Валюта має бути однією з: UAH, USD, EUR" |
| Відсутній `account_from_id` | `account_from_id` | "Рахунок відправника є обов'язковим" |
| Однакові рахунки | `account_to_id` | "Рахунки мають бути різними" |
| Неіснуючий рахунок | `account_from_id` або `account_to_id` | "Рахунок відправника/отримувача не знайдено" |

### Бізнес-помилки (422)

| Ситуація | `code` | `message` |
|----------|--------|-----------|
| Недостатньо коштів | `INSUFFICIENT_BALANCE` | "Недостатньо коштів на рахунку." |
| Однакові рахунки | `SAME_ACCOUNT_TRANSFER` | "Рахунок відправника і одержувача не можуть збігатися." |
---

## Валідація vs бізнес-логіка

Розмежування відповідальності між FormRequest (валідація) та Service (бізнес-правила).

### Таблиця розподілу

| Що перевіряється | Де перевіряється | Приклад |
|------------------|------------------|---------|
| Формат даних (required, string, integer, regex) | FormRequest | `amount` — додатнє число з максимум двома знаками після коми |
| Обов'язковість полів | FormRequest | `account_from_id` є обов'язковим |
| Допустимі значення (in, enum) | FormRequest | `currency` — тільки UAH, USD, EUR |
| Існування сутностей в БД (exists) | FormRequest | `account_from_id` існує в таблиці `accounts` |
| Синтаксичні правила (different, unique) | FormRequest | `account_from_id !== account_to_id` |
| Кількість елементів у масиві (min, max) | FormRequest | `items` має містити мінімум 1 позицію |
| Достатність коштів | Service | Баланс рахунку відправника ≥ сума переказу + комісія |
| Розрахунок комісії | Service | Комісія 0.5% для сум > 10000 грн |
| Статус рахунку (активний/заблокований) | Service | Рахунок має бути активним для виконання операції |
| Ліміти операцій | Service | Денний/місячний ліміт на перекази |
| Бізнес-процеси | Service | Відправка повідомлення, аудит, оновлення кешу |

### Правило розмежування

> **FormRequest** перевіряє **синтаксис** запиту: чи є всі поля, чи правильний формат, чи існують посилання на інші таблиці (проста перевірка `exists`).
>
> **Service** перевіряє **семантику** операції: чи можна виконати операцію з точки зору бізнесу (баланс, статуси, ліміти).

### Чому FormRequest не перевіряє баланс?

```php
// ❌ НЕПРАВИЛЬНО — баланс в FormRequest
'balance' => 'min:100'  // не працює, бо FormRequest не знає контекст

// ✅ ПРАВИЛЬНО — баланс в Service
if ($fromAccount->balance < $totalDeduct) {
    throw new InsufficientBalanceException();
}
```

**Причини:**
1. FormRequest не має доступу до даних БД (крім простої перевірки `exists`)
2. Баланс залежить від конкретного рахунку та суми переказу
3. Бізнес-правила змінюються частіше, ніж формат запиту
4. Тестування бізнес-логіки простіше в Service, ніж в FormRequest

### Приклад для POST /api/v1/transfers

| Поле/Правило | Де перевіряється | Чому |
|--------------|------------------|------|
| `account_from_id` required, integer, exists | FormRequest | Формат, існування в БД |
| `account_to_id` required, integer, exists, different | FormRequest | Формат, існування, не дорівнює from |
| `amount` required, string, regex | FormRequest | Формат числа з 2 знаками |
| `currency` required, in:UAH,USD,EUR | FormRequest | Допустимі валюти |
| `description` nullable, string, max:500 | FormRequest | Формат, максимальна довжина |
| Достатність балансу | Service | Потребує читання балансу з БД |
| Розрахунок комісії | Service | Залежить від суми переказу |

### Переваги такого підходу

1. **Чітка відповідальність** — кожен компонент робить своє
2. **Відсутність дублювання** — правила не повторюються в різних місцях
3. **Легке тестування** — можна тестувати валідацію окремо від бізнес-логіки
4. **Зручна зміна** — бізнес-правила можна змінювати без впливу на валідацію
5. **Єдина точка помилок** — всі бізнес-помилки повертають код та повідомлення через Exception Handler


## Документація API

### Swagger UI (інтерактивна документація)

Локальний доступ:
- Відкрийте браузер: [http://localhost:8000/swagger/](http://localhost:8000/swagger/)

### Альтернативний спосіб

1. Відкрийте https://editor.swagger.io/
2. Натисніть File → Import URL
3. Введіть: http://localhost:8000/api-docs/openapi.yaml

### Тестування API

- Отримання списку рахунків: `GET /api/v1/accounts`
- Отримання списку переказів: `GET /api/v1/transfers`
- Створення інвойсу: `POST /api/v1/invoices` (див. приклад у документації)

### Формат відповідей

Всі ендпоінти повертають JSON у форматі:
```json
{
    "data": { ... },
    "success": true,
    "message": "Операція виконана"
}
```

### Коди помилок

- 200: Успішний запит
- 201: Ресурс створено
- 404: Ресурс не знайдено
- 422: Помилка валідації (або бізнес-помилка)
---

Ти правий! Забагато. Давай зробимо мінімально і по суті.

# API Contract Validation Checklist

## Transfer API

| Ендпоінт | Метод | Статуси | Тест |
|----------|-------|---------|------|
| `/api/v1/transfers` | GET | 200 | `TransferApiTest::transfer_index_returns_200_with_list` |
| `/api/v1/transfers` | POST | 201, 422 | `TransferApiTest::transfer_returns_201_with_resource_structure_when_valid` |
| `/api/v1/transfers` | POST | 422 (validation) | `TransferApiTest::transfer_returns_422_with_errors_structure_when_validation_fails` |
| `/api/v1/transfers` | POST | 422 (insufficient balance) | `TransferApiTest::transfer_returns_422_with_code_when_insufficient_balance` |
| `/api/v1/transfers/{id}` | GET | 200, 404 | `TransferApiTest::test_transfer_show_returns_200_when_exists` |
| `/api/v1/transfers/{id}` | GET | 404 | `TransferApiTest::test_transfer_show_returns_404_when_not_found` |

## Account API

| Ендпоінт | Метод | Статуси | Тест |
|----------|-------|---------|------|
| `/api/v1/accounts` | GET | 200 | `AccountApiTest::account_index_returns_200` |
| `/api/v1/accounts/{id}` | GET | 200, 404 | `AccountApiTest::account_show_returns_200_with_balance` |
| `/api/v1/accounts/{id}/transactions` | GET | 200, 404 | `AccountApiTest::account_transactions_returns_200` |

## Invoice API

| Ендпоінт | Метод | Статуси | Тест |
|----------|-------|---------|------|
| `/api/v1/invoices` | POST | 201, 422 | `InvoiceApiTest::test_invoice_store_returns_201_when_valid` |
| `/api/v1/invoices` | POST | 422 (invalid items) | `InvoiceApiTest::test_invoice_store_returns_422_when_items_invalid` |
| `/api/v1/invoices/{id}` | GET | 404 | `InvoiceApiTest::test_invoice_show_returns_404_when_not_found` |

---

## Правило підтримки

> **Змінив ендпоінт → онови OpenAPI і тести**

Завжди тримай документацію синхронізованою з кодом.

```bash
#Перевірка
docker compose exec app php artisan test 
```
