# Pre-commit Hook

Pre-commit hook автоматично перевіряє код перед комітом:

- PHP-CS-Fixer (PSR-12 стандарти)
- PHPStan (статичний аналіз)

Якщо перевірки не проходять, коміт блокується.

## Встановлення

### 1. Зроби скрипт виконуваним

```bash
chmod +x scripts/pre-commit.sh
```

---

### 2. Встановити hook

```bash
# Копіювання
cp scripts/pre-commit.sh .git/hooks/pre-commit

# Або симлінк (рекомендовано)
ln -sf ../../scripts/pre-commit.sh .git/hooks/pre-commit
```

### 3. Перевірити що hook встановлено

```bash
ls -la .git/hooks/pre-commit
```

## Використання

При спробі зробити коміт:

```bash
git commit -m "message"
```

Hook автоматично запустить:

1. composer cs-check — перевірка стилю коду

2. composer stan — статичний аналіз

Якщо перевірки не проходять:

```bash
❌ Code style check failed!
Run 'composer cs-fix' to fix the issues.
```

## Оновлення

При спробі зробити коміт - оновлення скрипта автоматично застосовується до hook.

## Видалення

```bash
rm .git/hooks/pre-commit
```



