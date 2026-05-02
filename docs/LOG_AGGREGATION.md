# Потік логів CRM

## Архітектура

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  Laravel    │     │   Docker    │     │  Collector  │     │  Storage    │     │  Interface  │
│  (APP)      │ →   │   (stdout)  │ →   │  (Fluentd/  │ →   │  (Elastic-  │ →   │  (Kibana/   │
│  JSON logs  │     │   logs      │     │  Filebeat)  │     │  search/    │     │   Grafana)  │
└─────────────┘     └─────────────┘     └─────────────┘     │   Loki)     │     └─────────────┘
                                                            └─────────────┘
```

## Потік даних

### 1. Laravel → stdout

Laravel пише структуровані JSON-логи в `stderr` (або `stdout`):

```env
LOG_CHANNEL=stderr_json
```

Приклад JSON логу:

```json
{
    "message": "Transfer completed successfully",
    "context": {
        "correlation_id": "83a74f66-2847-4c87-b20a-651947a70529",
        "transfer_out_id": 30,
        "account_from_id": 1,
        "amount": 100.0
    },
    "level": 200,
    "level_name": "INFO",
    "datetime": "2026-04-28T12:42:29.634992+00:00"
}
```

### 2. Docker → перехоплення stdout

Docker автоматично перехоплює `stdout` контейнера:

```bash
# Перегляд логів контейнера
docker compose logs app

# Перегляд в реальному часі
docker compose logs -f app
```

### 3. Збирач (Collector) → читання логів

Збирач (Filebeat / Fluentd) читає логи Docker:

#### Filebeat конфігурація (`filebeat.yml`):

```yaml
filebeat.inputs:
- type: container
  paths:
    - /var/lib/docker/containers/*/*.log
  json.keys_under_root: true
  json.add_error_key: true

output.elasticsearch:
  hosts: ["elasticsearch:9200"]
  index: "crm-logs-%{+yyyy.MM.dd}"
```

#### Fluentd конфігурація (`fluentd.conf`):

```conf
<source>
  @type tail
  path /var/log/containers/*.log
  pos_file /var/log/fluentd-containers.log.pos
  tag docker.app
  format json
</source>

<match docker.app>
  @type elasticsearch
  host elasticsearch
  port 9200
  logstash_format true
  logstash_prefix crm-logs
</match>
```

### 4. Сховище → зберігання логів

| Сховище | Опис |
|---------|------|
| **Elasticsearch** | Повнотекстовий пошук, агрегації |
| **Loki** | Легковаговий, інтеграція з Grafana |
| **CloudWatch** | Для AWS деплоїв |
| **Sentry** | Для помилок (окремий потік) |

### 5. Інтерфейс → пошук та візуалізація

| Інструмент | Опис |
|------------|------|
| **Kibana** | Для Elasticsearch |
| **Grafana** | Для Loki / Elasticsearch |
| **Sentry** | Для помилок |

## Пошук логів

### Через Kibana

```bash
# Пошук за correlation_id
correlation_id: "83a74f66-2847-4c87-b20a-651947a70529"

# Пошук за рівнем
level_name: "ERROR"

# Пошук за модулем
context.account_from_id: 1
```

### Через командний рядок

```bash
# Локально
docker compose logs app | grep "correlation_id"

# Через jq (для JSON)
docker compose logs app 2>&1 | jq 'select(.context.correlation_id == "83a74f66...")'
```

## Приклад пошуку при інциденті

**Крок 1:** Знайти correlation_id в Sentry (тег `correlation_id`)

**Крок 2:** Пошук в Kibana:
```
correlation_id: "83a74f66-2847-4c87-b20a-651947a70529"
```

**Крок 3:** Побачити всі логи запиту:
- Вхідний запит
- Валідація
- TransferService
- Репозиторій
- Відповідь

## Переваги

| Без агрегації | З агрегацією |
|---------------|---------------|
| Потрібно знати, на якому сервері шукати | Один інтерфейс |
| grep вручну по файлах | Пошук за correlation_id |
| Не можна зв'язати логи різних сервісів | Легко зв'язати |
| Немає візуалізації | Дашборди та графіки |

## Висновок

```
App (JSON logs) → Docker (stdout) → Collector → Storage → Kibana/Grafana
```

Головне правило: **всі логи в одному місці, пошук за correlation_id за секунди.** 🔗

## Схема логів: Transfers

### Обов'язкові поля (верхній рівень JSON)

| Поле | Тип | Опис | Приклад |
|------|-----|------|---------|
| `level` | string | Рівень логування | `debug`, `info`, `warning`, `error`, `critical` |
| `message` | string | Короткий опис події | `"Transfer completed successfully"` |
| `timestamp` | string | Час події (ISO 8601) | `"2026-03-01T14:00:00.000000Z"` |
| `env` | string | Середовище виконання | `production`, `staging`, `local` |
| `module` | string | Модуль, що генерує лог | `transfers` |

### Контекст (в об'єкті `context`)

| Поле | Тип | Обов'язковість | Опис |
|------|-----|----------------|------|
| `correlation_id` | string | ✅ Завжди | Унікальний ID запиту |
| `transfer_id` | int | ⚠️ Якщо є | ID створеного переказу |
| `transfer_out_id` | int | ⚠️ Якщо є | ID вихідної транзакції |
| `transfer_in_id` | int | ⚠️ Якщо є | ID вхідної транзакції |
| `account_from_id` | int | ✅ Завжди | ID рахунку відправника |
| `account_to_id` | int | ✅ Завжди | ID рахунку отримувача |
| `amount` | float | ✅ Завжди | Сума переказу |
| `currency` | string | ✅ Завжди | Валюта (UAH, USD, EUR) |
| `commission` | float | ⚠️ Якщо є | Сума комісії |
| `balance` | float | ⚠️ Для помилок | Баланс рахунку при недостатньо коштів |
| `endpoint` | string | ✅ Завжди | HTTP метод та шлях |

---

### Приклад 1: Transfer completed (успішний переказ)

```json
{
    "level": "info",
    "message": "Transfer completed successfully",
    "timestamp": "2026-03-01T14:00:00.000000Z",
    "env": "local",
    "module": "transfers",
    "context": {
        "correlation_id": "83a74f66-2847-4c87-b20a-651947a70529",
        "transfer_out_id": 30,
        "transfer_in_id": 31,
        "account_from_id": 1,
        "account_to_id": 2,
        "amount": 100.0,
        "currency": "UAH",
        "commission": 0,
        "endpoint": "POST /api/v1/transfers"
    }
}
```

### Приклад 2: Transfer failed: insufficient balance

```json
{
    "level": "warning",
    "message": "Transfer failed: insufficient balance",
    "timestamp": "2026-03-01T14:00:00.000000Z",
    "env": "production",
    "module": "transfers",
    "context": {
        "correlation_id": "req-abc-123",
        "account_from_id": 10,
        "account_to_id": 20,
        "amount": 1000.00,
        "currency": "UAH",
        "balance": 500.00,
        "commission": 0,
        "endpoint": "POST /api/v1/transfers"
    }
}
```

### Приклад 3: Transfer failed: same account

```json
{
    "level": "warning",
    "message": "Transfer failed: same account",
    "timestamp": "2026-03-01T14:00:00.000000Z",
    "env": "production",
    "module": "transfers",
    "context": {
        "correlation_id": "req-abc-456",
        "account_from_id": 10,
        "account_to_id": 10,
        "amount": 100.00,
        "currency": "UAH",
        "endpoint": "POST /api/v1/transfers"
    }
}
```

### Приклад 4: Unexpected error (неочікувана помилка)

```json
{
    "level": "error",
    "message": "Transfer failed: unexpected error",
    "timestamp": "2026-03-01T14:00:00.000000Z",
    "env": "production",
    "module": "transfers",
    "context": {
        "correlation_id": "req-abc-789",
        "account_from_id": 10,
        "account_to_id": 20,
        "amount": 100.00,
        "currency": "UAH",
        "error_message": "Class \"Sentry\\Laravel\\Integration\" not found",
        "endpoint": "POST /api/v1/transfers"
    }
}
```

---

## Перевірка відповідності TransferService

### Поточне логування в TransferService

| Сценарій | Рівень | Чи відповідає схемі |
|----------|--------|---------------------|
| Transfer completed | `info` | ✅ Так |
| Transfer failed: insufficient balance | `warning` | ✅ Так |
| Transfer failed: same account | `warning` | ✅ Так |
| Transfer failed: account not found | `warning` | ✅ Так |
| Unexpected error | `error` | ✅ Так (через TransferErrorReporter) |

## Схема логів: Invoices

### Обов'язкові поля (верхній рівень JSON)

| Поле | Тип | Опис | Приклад |
|------|-----|------|---------|
| `level` | string | Рівень логування | `debug`, `info`, `warning`, `error`, `critical` |
| `message` | string | Короткий опис події | `"Invoice created successfully"` |
| `timestamp` | string | Час події (ISO 8601) | `"2026-03-01T14:00:00.000000Z"` |
| `env` | string | Середовище виконання | `production`, `staging`, `local` |
| `module` | string | Модуль, що генерує лог | `invoices` |

### Контекст (в об'єкті `context`)

| Поле | Тип | Обов'язковість | Опис |
|------|-----|----------------|------|
| `correlation_id` | string | ✅ Завжди | Унікальний ID запиту |
| `invoice_id` | int | ⚠️ Якщо є | ID створеного інвойсу |
| `client_id` | int | ✅ Завжди | ID клієнта |
| `total_amount` | float | ⚠️ Якщо є | Загальна сума інвойсу |
| `items_count` | int | ⚠️ Якщо є | Кількість позицій в інвойсі |
| `currency` | string | ⚠️ Якщо є | Валюта (UAH, USD, EUR) |
| `status` | string | ⚠️ Якщо є | Статус інвойсу (`draft`, `pending`, `paid`) |
| `endpoint` | string | ✅ Завжди | HTTP метод та шлях |

---

### Приклад 1: Invoice created (успішне створення)

```json
{
    "level": "info",
    "message": "Invoice created successfully",
    "timestamp": "2026-03-01T14:00:00.000000Z",
    "env": "local",
    "module": "invoices",
    "context": {
        "correlation_id": "83a74f66-2847-4c87-b20a-651947a70529",
        "invoice_id": 22,
        "client_id": 1,
        "total_amount": 1000.0,
        "items_count": 2,
        "currency": "UAH",
        "status": "draft",
        "endpoint": "POST /api/v1/invoices"
    }
}
```

### Приклад 2: Invoice creation failed: service not found

```json
{
    "level": "warning",
    "message": "Invoice creation failed: service not found",
    "timestamp": "2026-03-01T14:00:00.000000Z",
    "env": "production",
    "module": "invoices",
    "context": {
        "correlation_id": "req-abc-123",
        "client_id": 1,
        "service_id": 99999,
        "endpoint": "POST /api/v1/invoices"
    }
}
```

### Приклад 3: Invoice creation failed: client not found

```json
{
    "level": "warning",
    "message": "Invoice creation failed: client not found",
    "timestamp": "2026-03-01T14:00:00.000000Z",
    "env": "production",
    "module": "invoices",
    "context": {
        "correlation_id": "req-abc-456",
        "client_id": 99999,
        "endpoint": "POST /api/v1/invoices"
    }
}
```

### Приклад 4: Invoice creation failed: unexpected error

```json
{
    "level": "error",
    "message": "Invoice creation failed: unexpected error",
    "timestamp": "2026-03-01T14:00:00.000000Z",
    "env": "production",
    "module": "invoices",
    "context": {
        "correlation_id": "req-abc-789",
        "client_id": 1,
        "invoice_id": null,
        "total_amount": null,
        "items_count": 2,
        "error_message": "Database connection failed",
        "endpoint": "POST /api/v1/invoices"
    }
}
```

---

## Перевірка відповідності InvoiceService

### Поточне логування в InvoiceService

| Сценарій | Рівень | Чи відповідає схемі |
|----------|--------|---------------------|
| Invoice created | `info` | ✅ Так |
| Invoice creation failed: client not found | `warning` | ✅ Так |
| Invoice creation failed: service not found | `warning` | ✅ Так |
| Unexpected error | `error` | ✅ Так (через InvoiceErrorReporter) |

