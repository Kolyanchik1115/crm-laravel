# API Status Codes CRM

## Загальні статуси

| Код | Назва | Опис |
|-----|-------|------|
| 200 | OK | Запит успішний |
| 201 | Created | Ресурс успішно створено |
| 400 | Bad Request | Невірний формат запиту (наприклад, невалідний JSON) |
| 401 | Unauthorized | Необхідна автентифікація |
| 403 | Forbidden | Доступ заборонено (недостатньо прав) |
| 404 | Not Found | Ресурс не знайдено |
| 422 | Unprocessable Entity | Помилка валідації або бізнес-правила |
| 500 | Internal Server Error | Внутрішня помилка сервера |

---

## Таблиця статус-кодів за ендпоінтами

### Transfers (Перекази)

| Ендпоінт | Метод | 2xx | 4xx | 5xx |
|----------|-------|-----|-----|-----|
| `/api/v1/transfers` | GET | 200 OK | 400 (невірні параметри фільтрації), 401 | 500 |
| `/api/v1/transfers/{id}` | GET | 200 OK | 404 (переказ не знайдено), 401 | 500 |
| `/api/v1/transfers` | POST | 201 Created | 400 (невалідний JSON), 401, 422 (бізнес-помилки) | 500 |

#### 422 для POST /api/v1/transfers

| Причина | Повідомлення |
|---------|--------------|
| Однакові рахунки | "Cannot transfer to the same account" |
| Недостатньо коштів | "Insufficient funds for transfer" |
| Рахунок відправника не існує | "Account not found" |
| Рахунок отримувача не існує | "Account not found" |
| Сума менше 0.01 | "The amount must be at least 0.01" |
| Відсутнє поле `account_from_id` | "The from account id field is required" |
| Відсутнє поле `account_to_id` | "The to account id field is required" |
| Відсутнє поле `amount` | "The amount field is required" |

---

### Accounts (Рахунки)

| Ендпоінт | Метод | 2xx | 4xx | 5xx |
|----------|-------|-----|-----|-----|
| `/api/v1/accounts` | GET | 200 OK | 400 (невірні параметри), 401 | 500 |
| `/api/v1/accounts/{id}` | GET | 200 OK | 404 (рахунок не знайдено), 401 | 500 |
| `/api/v1/accounts/{id}/transactions` | GET | 200 OK | 404 (рахунок не знайдено), 400 (невірні параметри), 401 | 500 |

#### 422 для GET /api/v1/accounts

| Причина | Повідомлення |
|---------|--------------|
| Невірний формат `client_id` | "The client id must be an integer" |
| Невірний формат `currency` | "The currency must be 3 letters" |
| Невірне значення `currency` | "The selected currency is invalid" |

#### 404 для GET /api/v1/accounts/{id}

| Причина | Повідомлення |
|---------|--------------|
| Рахунок з ID не існує | "Account not found" |

---

### Invoices (Рахунки-фактури)

| Ендпоінт | Метод | 2xx | 4xx | 5xx |
|----------|-------|-----|-----|-----|
| `/api/v1/invoices` | GET | 200 OK | 400 (невірні параметри), 401 | 500 |
| `/api/v1/invoices/{id}` | GET | 200 OK | 404 (рахунок не знайдено), 401 | 500 |
| `/api/v1/invoices` | POST | 201 Created | 400 (невалідний JSON), 401, 422 (бізнес-помилки) | 500 |
| `/api/v1/invoices/{id}` | PATCH | 200 OK | 404 (рахунок не знайдено), 422 (невірний статус), 401 | 500 |

#### 422 для POST /api/v1/invoices

| Причина | Повідомлення |
|---------|--------------|
| Клієнт не існує | "Client not found" |
| Послуга не існує | "Service with ID {id} not found" |
| Немає позицій | "The items field is required" |
| Позиція без `service_id` | "The service id field is required" |
| Позиція без `quantity` | "The quantity field is required" |
| Позиція без `unit_price` | "The unit price field is required" |
| Кількість менше 1 | "The quantity must be at least 1" |
| Ціна менше 0 | "The unit price must be at least 0" |
| Невірна валюта | "The selected currency is invalid" |

#### 422 для PATCH /api/v1/invoices/{id}

| Причина | Повідомлення |
|---------|--------------|
| Невірний статус | "The selected status is invalid" |
| Статус не вказано | "The status field is required" |
| Рахунок не знайдено | "Invoice not found" |

---

## Схема помилок (Error Response Format)

### Валідаційна помилка (422)

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

### Бізнес-помилка (422)

```json
{
    "success": false,
    "message": "Insufficient funds for transfer"
}
```

### Ресурс не знайдено (404)

```json
{
    "success": false,
    "message": "Account not found"
}
```

### Внутрішня помилка сервера (500)

```json
{
    "success": false,
    "message": "Internal server error"
}
```

---

## Зведена таблиця

| Ендпоінт | Метод | 200 | 201 | 400 | 401 | 404 | 422 | 500 |
|----------|-------|-----|-----|-----|-----|-----|-----|-----|
| `/transfers` | GET | ✅ | - | ✅ | ✅ | - | - | ✅ |
| `/transfers/{id}` | GET | ✅ | - | - | ✅ | ✅ | - | ✅ |
| `/transfers` | POST | - | ✅ | ✅ | ✅ | - | ✅ | ✅ |
| `/accounts` | GET | ✅ | - | ✅ | ✅ | - | - | ✅ |
| `/accounts/{id}` | GET | ✅ | - | - | ✅ | ✅ | - | ✅ |
| `/accounts/{id}/transactions` | GET | ✅ | - | ✅ | ✅ | ✅ | - | ✅ |
| `/invoices` | GET | ✅ | - | ✅ | ✅ | - | - | ✅ |
| `/invoices/{id}` | GET | ✅ | - | - | ✅ | ✅ | - | ✅ |
| `/invoices` | POST | - | ✅ | ✅ | ✅ | - | ✅ | ✅ |
| `/invoices/{id}` | PATCH | ✅ | - | - | ✅ | ✅ | ✅ | ✅ |
