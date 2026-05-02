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
