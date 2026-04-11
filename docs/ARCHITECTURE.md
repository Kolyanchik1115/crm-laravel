# Архітектура CRM

## Схема шарів

```
┌────────────────────────────────────────────────────────────────────┐
│                             HTTP Request                           │
└────────────────────────────────────────────────────────────────────┘
│
▼
┌────────────────────────────────────────────────────────────────────┐
│                           Controller (Api)                         │
│  - Приймає Request                                                 │
│  - Валідація через FormRequest                                     │
│  - Створює DTO                                                     │
│  - Викликає Service                                                │
│  - Повертає Response (Resource або JSON)                           │
└────────────────────────────────────────────────────────────────────┘
│
▼
┌────────────────────────────────────────────────────────────────────┐
│                              Service                               │
│  - Бізнес-логіка (перевірки, розрахунки)                           │
│  - Координація репозиторіїв                                        │
│  - Транзакції БД                                                   │
│  - Оголошення подій (Event)                                        │
└────────────────────────────────────────────────────────────────────┘
│                       │
▼                       ▼
┌──────────────────────────────┐  ┌──────────────────────────────────┐
│        Repository            │  │              Event               │
│  - Запити до БД              │  │  - Сповіщення про подію          │
│  - Оновлення даних           │  │  - Викликає Listeners            │
└──────────────────────────────┘  └──────────────────────────────────┘
│                       │
▼                       ▼
┌──────────────────────────────┐  ┌──────────────────────────────────┐
│           Eloquent           │  │           Listener               │
│  - Моделі БД                 │  │  - Асинхронна реакція на подію   │
│  - Відношення                │  │  - Відправка Notification / Job  │
└──────────────────────────────┘  └──────────────────────────────────┘
│
▼
┌────────────────────────────────────────────────────────────────────┐
│                          MySQL / Redis                             │
└────────────────────────────────────────────────────────────────────┘
```

---

## Сценарій «Переказ коштів»

### Покроково:

```
1. HTTP Request (POST /api/v1/transfer)
   │
   ▼
2. TransferRequest (FormRequest)
    - Валідація: from_account_id, to_account_id, amount, currency
      │
      ▼
3. TransferController@transfer (Api)
    - toTransferDTO() → TransferDTO
    - Виклик $transferService->executeTransfer($dto)
      │
      ▼
4. TransferService@executeTransfer
    - Перевірка: рахунки різні
    - DB::transaction:
      • Отримання рахунків через AccountRepository
      • Розрахунок комісії (0.5% при сумі > 10000)
      • Перевірка достатності балансу
      • Оновлення балансів через AccountRepository
      • Створення транзакцій через TransactionRepository
    - event(new TransferCompleted)
      │
      ▼
5. TransferResource
    - Форматування відповіді
      │
      ▼
6. JSON Response
```

### Компоненти:

| Компонент               | Відповідальність            |
|-------------------------|-----------------------------|
| `TransferRequest`       | Валідація вхідних даних     |
| `TransferDTO`           | Передача даних між шарами   |
| `TransferController`    | HTTP шар                    |
| `TransferService`       | Бізнес-логіка               |
| `AccountRepository`     | Робота з рахунками (CRUD)   |
| `TransactionRepository` | Робота з транзакціями       |
| `TransferCompleted`     | Подія після переказу        |
| `TransferResource`      | Форматування JSON відповіді |

---

## Сценарій «Створення рахунку-фактури»

### Покроково:

```
1. HTTP Request (POST /api/v1/invoices)
   │
   ▼
2. StoreInvoiceRequest (FormRequest)
    - Валідація: client_id, items (service_id, quantity, unit_price)
      │
      ▼
3. CreateInvoiceController@store (Api)
    - toCreateInvoiceDTO() → CreateInvoiceDTO
    - Виклик $invoiceService->createInvoice($dto)
      │
      ▼
4. InvoiceService@createInvoice
    - Перевірка існування послуг через ServiceRepository
    - DB::transaction:
      • Розрахунок загальної суми
      • Створення invoice через InvoiceRepository
      • Створення invoice_items через InvoiceItemRepository
    - event(new InvoiceCreated)
      │
      ▼
5. InvoiceResource
    - Форматування відповіді
      │
      ▼
6. JSON Response
```

### Компоненти:

| Компонент                 | Відповідальність            |
|---------------------------|-----------------------------|
| `StoreInvoiceRequest`     | Валідація вхідних даних     |
| `CreateInvoiceDTO`        | Передача даних між шарами   |
| `CreateInvoiceController` | HTTP шар                    |
| `InvoiceService`          | Бізнес-логіка               |
| `InvoiceRepository`       | Створення інвойсу           |
| `InvoiceItemRepository`   | Створення позицій           |
| `ServiceRepository`       | Перевірка існування послуг  |
| `InvoiceCreated`          | Подія після створення       |
| `InvoiceResource`         | Форматування JSON відповіді |

---

## Таблиця відповідальностей

| Що                       | Де                   | Приклад                                             |
|--------------------------|----------------------|-----------------------------------------------------|
| **HTTP запит/відповідь** | `Controller` (Api)   | `TransferController`, `CreateInvoiceController`     |
| **Валідація даних**      | `FormRequest`        | `TransferRequest`, `StoreInvoiceRequest`            |
| **Передача даних**       | `DTO`                | `TransferDTO`, `CreateInvoiceDTO`                   |
| **Бізнес-логіка**        | `Service`            | `TransferService`, `InvoiceService`                 |
| **Робота з БД**          | `Repository`         | `AccountRepository`, `TransactionRepository`        |
| **Події**                | `Event` + `Listener` | `TransferCompleted`, `InvoiceCreated`               |
| **Асинхронні задачі**    | `Job`                | `SendTransferConfirmationJob`, `LogInvoiceAuditJob` |
| **Сповіщення**           | `Notification`       | `TransferConfirmationNotification`                  |
| **Форматування JSON**    | `Resource`           | `TransferResource`, `InvoiceResource`               |
| **Кешування**            | `Cache` (в Service)  | `ClientService::getAllClients`                      |

---

## Правила для розробників

### Золоте правило:

> **Якщо сумніваєшся, куди покласти логіку — НЕ в контролер. Краще в сервіс.**

### Просте правило:

| Тип логіки                                | Куди                 |
|-------------------------------------------|----------------------|
| HTTP, статус-коди, відповіді              | **Controller**       |
| Валідація полів                           | **FormRequest**      |
| Перевірки бізнес-правил (баланс, комісія) | **Service**          |
| Запити до БД (Eloquent)                   | **Repository**       |
| Асинхронні задачі                         | **Job**              |
| Події та реакції                          | **Event + Listener** |
| Сповіщення користувачів                   | **Notification**     |
| Форматування API відповіді                | **Resource**         |

### Контролер має бути ТОНКИМ:

```php
// ✅ Добре
public function transfer(TransferRequest $request): JsonResponse
{
    $dto = $request->toTransferDTO();
    $result = $this->transferService->executeTransfer($dto);
    return (new TransferResource($result))->additional(['success' => true])->response();
}

// ❌ Погано (бізнес-логіка в контролері)
public function transfer(Request $request): JsonResponse
{
    $account = Account::find($request->from_account_id);
    if ($account->balance < $request->amount) {
        throw new Exception('Insufficient funds');
    }
    // ... багато коду
}
```

---

## Переваги архітектури

| Перевага                        | Опис                                          |
|---------------------------------|-----------------------------------------------|
| **Розділення відповідальності** | Кожен шар знає тільки своє                    |
| **Тестування**                  | Легко тестувати кожен шар окремо              |
| **Підтримка**                   | Легко знайти, де шукати проблему              |
| **Перевикористання**            | Сервіси можна використовувати в різних місцях |
| **Масштабування**               | Легко додавати нові функції                   |
