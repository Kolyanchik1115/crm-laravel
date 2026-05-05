## Інструменти профайлінгу

### Laravel Telescope (локально)

#### Встановлення

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```
### Laravel Debugbar (додатково)

#### Встановлення

```bash
composer require barryvdh/laravel-debugbar --dev
```

#### Перевірка

Після встановлення внизу сторінки з'явиться панель з інформацією про:
- SQL запити (кількість, час)
- Пам'ять
- Час виконання

#### Результат для GET /api/v1/transfers

| Показник | Значення |
|----------|----------|
| Статус | 200 ✅ |
| Загальний час | 84ms |
| Кількість SQL запитів | 2 (основний) + додаткові (auth, roles) |


![Telescope Requests](docs/images/telescope-transfers-requests.png)
![Telescope Queries](docs/images/telescope-transfers-query.png)
![Debugbar](docs/images/debugbar.png)
