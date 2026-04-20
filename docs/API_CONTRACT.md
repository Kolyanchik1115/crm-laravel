# API Contract CRM

## Версіонування

Всі API ендпоінти використовують префікс `/api/v1/` для підтримки зворотної сумісності.

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

## Діаграма послідовності (Sequence Diagram)

```
Клієнт          API Gateway      TransferController    TransferService         Repository         БД
  │                   │                     │                      │                 │             │
  │──POST /transfers─▶│                     │                      │                 │             │
  │                   │──▶validate request─▶│                      │                 │             │
  │                   │                     │──▶executeTransfer()─▶│                 │             │
  │                   │                     │                      │───findById()───▶│───SELECT───▶│
  │                   │                     │                      │◀──Account──────│◀─────────────│
  │                   │                     │                      │───decrement()──▶│───UPDATE───▶│
  │                   │                     │                      │───increment()──▶│───UPDATE───▶│
  │                   │                     │                      │───create()─────▶│───INSERT───▶│
  │                   │                     │◀───result────────────│                 │             │
  │                   │◀───201 Created──────│                      │                 │             │
  │◀───Response───────│                     │                      │                 │             │
```

---

## Зміни в майбутніх версіях

### Плановані зміни в v2

- Додавання пагінації з курсором (cursor-based pagination)
- Додавання фільтрації за статусом переказу
- Додавання Webhooks для сповіщення про нові перекази

---

# Версіонування

Всі API ендпоінти використовують префікс `/api/v1/` для підтримки зворотної сумісності.

---

## Ресурс: Accounts (Рахунки)

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
  -H "Content-Type: application/json" \
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

```
/api/v1/
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
