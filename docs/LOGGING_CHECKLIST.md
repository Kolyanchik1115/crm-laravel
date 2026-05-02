# Production-Ready Logging Checklist

## Правило

> **Перед деплоєм в production перевірити, що всі пункти виконані.**
>
> Відповідальний: Team Lead + on-call engineer.

---

## Чеклист

| # | Вимога | Статус | Документація |
|---|--------|--------|--------------|
| 1 | **Структуровані логи (JSON)** | ☐ | [LOG_AGGREGATION.md](./LOG_AGGREGATION.md) |
| 2 | **Рівні логування** (debug, info, warning, error, critical) | ☐ | [LOGGING_GUIDE.md](./LOGGING_GUIDE.md) |
| 3 | **Correlation ID у кожному запиті** (middleware) | ☐ | [LOGGING_GUIDE.md](./LOGGING_GUIDE.md) |
| 4 | **Контекст у критичних подіях** (transfer_id, invoice_id, account_id) | ☐ | [LOGGING_GUIDE.md](./LOGGING_GUIDE.md) |
| 5 | **Логи пишуться в stdout** (для збирача) | ☐ | [LOG_AGGREGATION.md](./LOG_AGGREGATION.md) |
| 6 | **Sentry інтегровано**, очікувані помилки виключені | ☐ | [ERROR_TRACKING.md](./ERROR_TRACKING.md) |
| 7 | **Correlation ID у Sentry scope** | ☐ | [ERROR_TRACKING.md](./ERROR_TRACKING.md) |
| 8 | **TransferErrorReporter**, **InvoiceErrorReporter** | ☐ | [ERROR_TRACKING.md](./ERROR_TRACKING.md) |
| 9 | **Схема логів задокументована** (Transfers, Invoices) | ☐ | [LOG_AGGREGATION.md](./LOG_AGGREGATION.md) |
| 10 | **INCIDENT_RUNBOOK з повним циклом** | ☐ | [INCIDENT_RUNBOOK.md](./INCIDENT_RUNBOOK.md) |
| 11 | **POST_MORTEM_TEMPLATE** | ☐ | [POST_MORTEM_TEMPLATE.md](./POST_MORTEM_TEMPLATE.md) |
| 12 | **Типові запити для лог-агрегації** | ☐ | [LOG_AGGREGATION.md](./LOG_AGGREGATION.md) |

---

## Перевірка перед деплоєм

### Крок 1: Перевірити конфігурацію

```bash
# Перевірити LOG_CHANNEL
docker compose exec app env | grep LOG_CHANNEL
# Має бути: LOG_CHANNEL=stderr_json

# Перевірити Sentry DSN
docker compose exec app php artisan tinker
>>> config('sentry.dsn')
>>> exit
```

### Крок 2: Перевірити логи в Elasticsearch

```bash
# Перевірити індекси
curl -X GET "http://localhost:9200/_cat/indices?v"

# Перевірити останні логи
curl -X GET "http://localhost:9200/filebeat-*/_search?size=1&sort=@timestamp:desc"
```

### Крок 3: Перевірити Sentry

```bash
# Відправити тестову подію
php artisan sentry:test

# Перевірити що подія з'явилась в Sentry
```

### Крок 4: Перевірити correlation_id

```bash
# Зробити запит
curl -X POST http://localhost:8000/api/v1/transfers \
  -H "Content-Type: application/json" \
  -d '{"account_from_id":1,"account_to_id":2,"amount":"100","currency":"UAH"}'

# Перевірити заголовок
curl -v http://localhost:8000/api/v1/transfers 2>&1 | grep "X-Correlation-Id"
```

### Крок 5: Запустити всі тести

```bash
docker compose exec app php artisan test
```

---

## Що робити при пропуску пункту

| Пункт | Дія |
|-------|-----|
| 1-2 | Оновити `config/logging.php` та `.env` |
| 3 | Додати/оновити `AddCorrelationId` middleware |
| 4 | Оновити `TransferService` та `InvoiceService` |
| 5 | Переконатись що `LOG_CHANNEL=stderr_json` |
| 6-8 | Перевірити `config/sentry.php` та `before_send` |
| 9-12 | Оновити відповідну документацію |

---

## Підписання

Перед деплоєм в production:

- **Team Lead:** _________________ (дата: ______)
- **On-call Engineer:** _________________ (дата: ______)
- **QA:** _________________ (дата: ______)

---

## Посилання на документацію

| Документ | Опис |
|----------|------|
| [LOGGING_GUIDE.md](./LOGGING_GUIDE.md) | Карта подій, рівні логування, correlation_id |
| [ERROR_TRACKING.md](./ERROR_TRACKING.md) | Sentry налаштування, фільтрація, reporters |
| [INCIDENT_RUNBOOK.md](./INCIDENT_RUNBOOK.md) | Сценарій інциденту, дії команди |
| [LOG_AGGREGATION.md](./LOG_AGGREGATION.md) | Потік логів, схема, типові запити |
| [POST_MORTEM_TEMPLATE.md](./POST_MORTEM_TEMPLATE.md) | Шаблон аналізу інцидентів |

---

## Версіонування

| Версія | Дата | Зміни | Автор |
|--------|------|-------|-------|
| 1.0 | 2026-05-02 | Початкова версія | Team |
