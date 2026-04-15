#!/bin/sh

echo "========================================="
echo "Running pre-commit checks..."
echo "========================================="

# Перевірка кодстайлу
echo "🔍 Running code style check (PHP CS Fixer)..."
composer cs-check
CS_STATUS=$?

if [ "$CS_STATUS" -ne 0 ]; then
    echo ""
    echo "❌ Code style check failed!"
    echo "Please run: composer cs-fix"
    echo "Then add the fixed files and commit again."
    echo "========================================="
    exit 1
fi
echo "✅ Code style check passed!"
echo ""

# Запуск PHPStan
echo "🔍 Running PHPStan static analysis..."
composer stan
STAN_STATUS=$?

if [ "$STAN_STATUS" -ne 0 ]; then
    echo ""
    echo "❌ PHPStan failed!"
    echo "Please fix the errors above before committing."
    echo "========================================="
    exit 1
fi
echo "✅ PHPStan passed!"
echo ""

echo "========================================="
echo "✅ All pre-commit checks passed!"
echo "========================================="
exit 0
