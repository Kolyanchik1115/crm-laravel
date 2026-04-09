# Асинхронна архітектура CRM: Jobs, Events, Notifications

## Вступ

У CRM використовуються три основні механізми для асинхронної обробки:
- **Jobs** — конкретні задачі
- **Events** — події, на які реагують Listeners
- **Notifications** — сповіщення користувачів

---

## 1. Job (Задача)

### Що це?
Одна конкретна задача з чіткою точкою запуску. Job виконує одну дію і завершується.

### Коли використовувати?
- Генерація звітів
- Перевірка статусів
- Імпорт/експорт даних
- Відправка email (якщо не через Notification)
- Оновлення кешу

### Приклади з CRM
| Job | Що робить |
|-----|-----------|
| `DailyCrmReportJob` | Генерує щоденний звіт |
| `UpdateDashboardCacheJob` | Оновлює кеш дашборду |
| `SendTransferConfirmationJob` | Відправляє підтвердження переказу |

### Виклик
```php
// Прямий виклик
SendTransferConfirmationJob::dispatch($transactionId)->onQueue('notifications');

// З затримкою
UpdateDashboardCacheJob::dispatch()->delay(now()->addSeconds(30));
```

---

## 2. Event (Подія)

### Що це?
Факт, що щось сталося. Подія не знає, хто і як на неї реагує. Listeners визначають реакції.

### Коли використовувати?
- Одна подія → багато незалежних реакцій
- Коли плануєш додавати нові реакції в майбутньому
- Для розділення відповідальності

### Приклади з CRM
| Event | Що сталося | Listeners |
|-------|-------------|-----------|
| `TransferCompleted` | Переказ виконано | Email, аудит, оновлення кешу |
| `InvoiceCreated` | Рахунок створено | Аудит, оновлення кешу, сповіщення |

### Виклик
```php
// В контролері або сервісі
event(new TransferCompleted(
transactionOutId: $transactionOut->id,
accountFromId: $fromAccount->id,
accountToId: $toAccount->id,
amount: (string) $amount,
currency: $fromAccount->currency,
));
```

### Реєстрація Listeners
```php
// app/Providers/EventServiceProvider.php
protected $listen = [
TransferCompleted::class => [
SendTransferConfirmationNotification::class,
LogTransferToAudit::class,
UpdateDashboardCacheListener::class,
],
];
```

---

## 3. Notification (Сповіщення)

### Що це?
Доставка повідомлення користувачу через різні канали (email, database, Slack, SMS).

### Коли використовувати?
- Сповіщення клієнта про операцію
- Нагадування
- Підтвердження дій

### Приклади з CRM
| Notification | Канали | Коли відправляється |
|--------------|--------|---------------------|
| `TransferConfirmationNotification` | mail, database | Після переказу |
| `InvoiceCreatedNotification` | mail, database | Після створення рахунку (з затримкою) |

### Виклик
```php
// Через Notifiable trait
$client->notify(new TransferConfirmationNotification(
transactionId: $transactionId,
amount: (string) $amount,
currency: $currency,
isReceiver: false,
));

// З затримкою
$client->notify(
(new InvoiceCreatedNotification(...))->delay(now()->addMinutes(3))
);
```

---

## Схема взаємодії

```
Controller / Service
│
▼
event(new TransferCompleted(...))
│
▼
┌───────────────────────────────────────────────┐
│              EventServiceProvider             │
│  TransferCompleted::class => [                │
│      SendTransferConfirmationNotification,    │
│      LogTransferToAudit,                      │
│      UpdateDashboardCacheListener,            │
│  ]                                            │
└───────────────────────────────────────────────┘
│
├──────────────────┬──────────────────┐
▼                  ▼                  ▼
Listener 1         Listener 2         Listener 3
│                  │                  │
▼                  ▼                  ▼
Notification         Audit Log           Job
(email/db)           (database)          (cache)
```

---

## Порівняльна таблиця

| Аспект | Job | Event | Notification |
|--------|-----|-------|--------------|
| **Призначення** | Виконати одну задачу | Повідомити про подію | Сповістити користувача |
| **Знає про реакції** | Так (жорстко) | Ні (гнучко) | Так (жорстко) |
| **Кількість реакцій** | Одна | Багато | Одна (але кілька каналів) |
| **Додавання нової логіки** | Зміна коду виклику | Новий Listener (без змін) | Новий канал в Notification |
| **Черга** | Так (ShouldQueue) | Так (через Listener) | Так (ShouldQueue) |
| **Приклад** | `DailyCrmReportJob` | `TransferCompleted` | `InvoiceCreatedNotification` |

---

## Коли що використовувати?

| Ситуація | Рішення |
|----------|---------|
| Потрібно виконати одну конкретну дію | **Job** |
| Подія може викликати багато різних дій | **Event** |
| Плануєш додавати нові реакції в майбутньому | **Event** |
| Потрібно сповістити користувача | **Notification** |
| Потрібно сповістити через кілька каналів | **Notification** |
| Задача, яка не пов'язана з користувачем | **Job** |

---

## Приклад з CRM: Переказ коштів

```
Користувач робить переказ
│
▼
TransferController
│
▼
TransferService (DB::transaction)
│
▼
event(new TransferCompleted(...))
│
├──────────────────────────────────────────┐
▼                                          ▼
SendTransferConfirmationNotification      LogTransferToAudit
│                                          │
▼                                          ▼
TransferConfirmationNotification           Запис в audit_log
(відправник і отримувач)
│
▼
Таблиця notifications + Email
```

## Висновок

- **Job** = "Зроби це" (одна задача)
- **Event** = "Це сталося" (хто хоче — реагує)
- **Notification** = "Сповісти користувача" (email, БД, etc.)
