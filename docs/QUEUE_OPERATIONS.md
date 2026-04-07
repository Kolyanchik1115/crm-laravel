# Перегляд failed jobs

```bash
# Docker
docker compose exec app php artisan queue:failed

# Локально
php artisan queue:failed
```

## Деталі failed job

```bash
# Docker
docker compose exec app php artisan queue:failed <id>

# Локально
php artisan queue:failed <id>
```

## Повторення failed jobs

```bash
# Docker
docker compose exec app php artisan queue:retry <id>
docker compose exec app php artisan queue:retry all

# Локально
php artisan queue:retry <id>
php artisan queue:retry all
```

## Видалення failed jobs

```bash
# Docker
docker compose exec app php artisan queue:forget <id>
docker compose exec app php artisan queue:flush

# Локально
php artisan queue:forget <id>
php artisan queue:flush
```

## Запуск worker (обробка черг)

```bash
# Docker
docker compose exec app php artisan queue:work redis --sleep=3 --tries=3

# Локально
php artisan queue:work redis --sleep=3 --tries=3
```

## Очищення кешу

```bash
# Docker
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear

# Локально
php artisan cache:clear
php artisan config:clear
```

## Перевірка черги в Redis

```bash
# Docker
docker compose exec redis redis-cli LLEN queues:default

# Локально
redis-cli LLEN queues:default
```
