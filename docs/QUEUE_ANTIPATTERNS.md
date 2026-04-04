# Коли НЕ використовувати черги (ANTI-PATTERNS)

### 1. Критична логіка в запиті

**Що це означає:**
Логіка, без якої неможливо повернути коректну відповідь користувачу.

**Приклади з CRM:**
- ✅ Збереження платежу
- ✅ Оновлення балансу рахунку
- ✅ Створення транзакції
- ✅ Зняття коштів з одного рахунку та поповнення іншого

**Чому не в чергу:**
> Користувач повинен отримати відповідь "Переказ успішно виконано" ТІЛЬКИ після того, як гроші реально списані та зараховані.

```php
// ❌ Критична логіка в черзі
class TransferJob implements ShouldQueue
{
    public function handle(): void
    {
        // Зняття коштів
        $fromAccount->balance -= $this->amount;
        $fromAccount->save();
        
        // Поповнення коштів
        $toAccount->balance += $this->amount;
        $toAccount->save();
    }
}

// ✅ Критична логіка синхронно
public function transfer(Request $request): JsonResponse
{
    DB::transaction(function () {
        $fromAccount->balance -= $amount;
        $fromAccount->save();
        
        $toAccount->balance += $amount;
        $toAccount->save();
    });
    
    return response()->json(['success' => true]);
}
```

---

### 2. Прості обчислення

**Що це означає:**
Операції, які виконуються швидко (мілісекунди) і не потребують окремого процесу.

**Приклади з CRM:**
- Валідація вхідних даних
- Перерахування суми кошика
- Підрахунок кількості позицій в рахунку
- Форматування даних

**Чому не в чергу:**
> Валідація та прості обчислення виконуються за 1-10 мс. Відправка в чергу додасть затримку без жодної користі.

```php
// ❌ Просте обчислення в черзі
class CalculateTotalJob implements ShouldQueue
{
    public function handle(): void
    {
        $total = collect($this->items)->sum('price');
    }
}

// ✅ Синхронно
public function calculateTotal(Request $request): JsonResponse
{
    $total = collect($request->items)->sum('price');
    return response()->json(['total' => $total]);
}
```

---

### 3. Результат очікується одразу

**Що це означає:**
Користувач чекає на результат операції для продовження роботи.

**Приклади з CRM:**
- Пошук клієнтів
- Фільтрація списку рахунків
- Перевірка унікальності email
- Авторизація користувача

**Чому не в чергу:**
> Якщо користувач чекає на результат, асинхронність не дає переваг - лише додає складність.

```php
// ❌ Пошук в черзі
class SearchClientsJob implements ShouldQueue
{
    public function handle(): void
    {
        $results = Client::where('name', 'like', "%{$this->query}%")->get();
    }
}

// ✅ Синхронно
public function search(Request $request): JsonResponse
{
    $results = Client::where('name', 'like', "%{$request->query}%")->get();
    return response()->json($results);
}
```

---

## Приклад з CRM: Переказ між рахунками

| Операція | Синхронно / Асинхронно | Чому |
|----------|----------------------|------|
| **UPDATE балансів** | 🔴 Синхронно | Критично для відповіді |
| **INSERT транзакцій** | 🔴 Синхронно | Без цього переказ не підтверджено |
| **Відправка email** | 🟢 Асинхронно (черга) | Не впливає на факт переказу |
| **Запис аудит-логу** | 🟢 Асинхронно (черга) | Допоміжна операція |
| **Оновлення кешу** | 🟢 Асинхронно (черга) | Покращує продуктивність |

```php
class TransferController extends Controller
{
    public function store(TransferRequest $request): JsonResponse
    {
        // 🔴 Синхронно - критична логіка
        DB::transaction(function () use ($request) {
            $fromAccount->balance -= $request->amount;
            $fromAccount->save();
            
            $toAccount->balance += $request->amount;
            $toAccount->save();
            
            Transaction::create([...]);
        });
        
        // 🟢 Асинхронно - допоміжні операції
        SendTransferEmail::dispatch($transaction);
        LogAuditEvent::dispatch($transaction);
        ClearCache::dispatch();
        
        return response()->json(['success' => true]);
    }
}
```

---

## Правило "Мінімум у запиті"

> **У запиті залишаємо мінімум - те, без чого неможливо повернути коректну відповідь. Решту - у чергу.**

### Як застосовувати:

1. **Визнач, що потрібно для відповіді**
    - Що користувач повинен знати негайно?

2. **Відокрем критичну логіку**
    - UPDATE балансів, INSERT транзакцій

3. **Все інше - в чергу**
    - Email, аудит, кеш, повільні API

## Висновок

| Коли НЕ використовувати черги | Чому |
|------------------------------|------|
| Критична логіка (UPDATE балансу) | Користувач повинен знати результат негайно |
| Прості обчислення (валідація) | Виконується швидко (<10 мс) |
| Результат очікується одразу (пошук) | Користувач чекає на відповідь |

**Основне правило:**
> Синхронно - мінімум, без чого не можна відповісти.
> Асинхронно - все інше, що може виконатись пізніше.

