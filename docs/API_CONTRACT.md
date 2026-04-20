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
