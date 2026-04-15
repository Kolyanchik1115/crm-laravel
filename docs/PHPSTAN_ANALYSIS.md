# PHPStan Аналіз коду CRM

## Конфігурація

- **Рівень:** 5
- **Перевірені директорії:** `app/`
- **Кількість знайдених помилок:** 79

---

## Знайдені помилки

### 1. Web-контролери (return type View)

| Файл                        | Рядок      | Помилка                                              | Рішення                            |
|-----------------------------|------------|------------------------------------------------------|------------------------------------|
| `AccountController.php`     | 26, 36     | `should return View but returns Contracts\View\View` | Змінити `use Illuminate\View\View` |
| `ClientController.php`      | 28, 38, 43 | `should return View but returns Contracts\View\View` | Змінити `use Illuminate\View\View` |
| `DashboardController.php`   | 23         | `should return View but returns Contracts\View\View` | Змінити `use Illuminate\View\View` |
| `TransactionController.php` | 26         | `should return View but returns Contracts\View\View` | Змінити `use Illuminate\View\View` |

---

### 2. API контролери (не вистачає PHPDoc)

| Файл                        | Рядок  | Помилка                                              | Рішення                        |
|-----------------------------|--------|------------------------------------------------------|--------------------------------|
| `Api/ServiceController.php` | 16, 25 | `Call to undefined static method Service::orderBy()` | Додати PHPDoc в модель Service |

---

### 3. Jobs (не вистачає PHPDoc та імпортів)

| Файл                                | Помилка                                       | Рішення                            |
|-------------------------------------|-----------------------------------------------|------------------------------------|
| `DailyCrmReportJob.php`             | `Call to undefined static method whereDate()` | Додати PHPDoc в модель             |
| `LogInvoiceAuditJob.php`            | `Access to undefined property`                | Додати PHPDoc в модель Invoice     |
| `SendTransferConfirmationJob.php`   | `Access to undefined property`                | Додати PHPDoc в модель Transaction |
| `SendUnpaidInvoiceRemindersJob.php` | `Call to undefined static method whereIn()`   | Додати PHPDoc в модель Invoice     |
| `UpdateDashboardCacheJob.php`       | `Call to undefined static method whereDate()` | Додати PHPDoc в модель             |

---

### 4. Репозиторії (return type Collection)

| Файл                        | Помилка                                                          | Рішення                                                                                      |
|-----------------------------|------------------------------------------------------------------|----------------------------------------------------------------------------------------------|
| `AccountRepository.php`     | `should return Collection but returns Collection<int, stdClass>` | Змінити `use Illuminate\Support\Collection` на `use Illuminate\Database\Eloquent\Collection` |
| `ClientRepository.php`      | `should return Collection but returns Collection<int, stdClass>` | Змінити `use Illuminate\Support\Collection` на `use Illuminate\Database\Eloquent\Collection` |
| `InvoiceRepository.php`     | `should return Collection but returns Collection<int, stdClass>` | Змінити `use Illuminate\Support\Collection` на `use Illuminate\Database\Eloquent\Collection` |
| `TransactionRepository.php` | `should return Collection but returns Collection<int, stdClass>` | Змінити `use Illuminate\Support\Collection` на `use Illuminate\Database\Eloquent\Collection` |

---

### 5. Невистачає `use` для моделей

| Файл                        | Помилка                                                 | Рішення                              |
|-----------------------------|---------------------------------------------------------|--------------------------------------|
| `AccountRepository.php`     | `Call to undefined static method Account::find()`       | Додати `use App\Models\Account;`     |
| `ClientRepository.php`      | `Call to undefined static method Client::create()`      | Додати `use App\Models\Client;`      |
| `InvoiceRepository.php`     | `Call to undefined static method Invoice::create()`     | Додати `use App\Models\Invoice;`     |
| `ServiceRepository.php`     | `Call to undefined static method Service::where()`      | Додати `use App\Models\Service;`     |
| `TransactionRepository.php` | `Call to undefined static method Transaction::create()` | Додати `use App\Models\Transaction;` |

---

### 6. Access to undefined property

| Файл                    | Помилка                                           | Рішення                        |
|-------------------------|---------------------------------------------------|--------------------------------|
| `AccountRepository.php` | `Access to undefined property $balance`           | Додати PHPDoc в модель Account |
| `InvoiceService.php`    | `Access to undefined property $id, $total_amount` | Додати PHPDoc в модель Invoice |
| `TransferService.php`   | `Access to undefined property $balance, $id`      | Додати PHPDoc в модель Account |

---

## Статистика помилок

| Категорія               | Кількість |
|-------------------------|-----------|
| Return type View        | 7         |
| Undefined static method | 25        |
| Undefined property      | 30        |
| Return type Collection  | 6         |
| Missing import          | 10        |
| Інші                    | 1         |
| **ВСЬОГО**              | **79**    |
