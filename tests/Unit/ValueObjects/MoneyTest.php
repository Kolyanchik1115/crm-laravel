<?php

declare(strict_types=1);

namespace Tests\Unit\ValueObjects;

use App\ValueObjects\Money;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MoneyTest extends TestCase
{
    // ===== CONSTRUCTOR TESTS =====

    #[Test] // Annotation tells PHPUnit that a method is a test method
    public function money_creates_with_valid_amount_and_currency(): void
    {
        $money = new Money('100.00', 'UAH');

        $this->assertSame('100.00', $money->amount);
        $this->assertSame('UAH', $money->currency);
        $this->assertSame(100.0, $money->getValue());
    }

    #[Test]
    public function money_creates_with_valid_amount_without_currency_defaults_to_uah(): void
    {
        $money = new Money('50.00');

        $this->assertSame('50.00', $money->amount);
        $this->assertSame('UAH', $money->currency);
    }

    #[Test]
    public function money_throws_on_negative_amount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount must be positive.');

        new Money('-10.00', 'UAH');
    }

    #[Test]
    public function money_throws_on_zero_amount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount must be positive.');

        new Money('0', 'UAH');
    }

    #[Test]
    public function money_throws_on_zero_amount_with_decimal(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount must be positive.');

        new Money('0.00', 'UAH');
    }

    #[Test]
    public function money_throws_on_invalid_currency_too_short(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Currency must be 3-letter code.');

        new Money('100.00', 'UA');
    }

    #[Test]
    public function money_throws_on_invalid_currency_too_long(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Currency must be 3-letter code.');

        new Money('100.00', 'UAHH');
    }

    // ===== IS_GREATER_THAN METHOD TESTS =====

    #[Test]
    public function is_greater_than_returns_true_when_amount_exceeds_threshold(): void
    {
        $money = new Money('1500', 'UAH');

        $result = $money->isGreaterThan('1000');

        $this->assertTrue($result);
    }

    #[Test]
    public function is_greater_than_returns_false_when_amount_below_threshold(): void
    {
        $money = new Money('500', 'UAH');

        $result = $money->isGreaterThan('1000');

        $this->assertFalse($result);
    }

    #[Test]
    public function is_greater_than_returns_false_when_amount_equals_threshold(): void
    {
        $money = new Money('1000', 'UAH');

        $result = $money->isGreaterThan('1000');

        $this->assertFalse($result);
    }

    #[Test]
    public function is_greater_than_works_with_decimal_values(): void
    {
        $money = new Money('1000.50', 'UAH');

        $result = $money->isGreaterThan('1000');

        $this->assertTrue($result);
    }

    // ===== ADD METHOD TESTS =====

    #[Test]
    public function add_returns_new_money_with_sum(): void
    {
        $money = new Money('100.00', 'UAH');

        $newMoney = $money->add('50.00');

        $this->assertEquals(150.0, (float) $newMoney->amount);
        $this->assertSame('UAH', $newMoney->currency);
    }

    #[Test]
    public function add_returns_new_money_with_sum_of_decimal_values(): void
    {
        $money = new Money('100.50', 'UAH');

        $newMoney = $money->add('49.50');

        $this->assertEquals(150.0, (float) $newMoney->amount);
        $this->assertSame('UAH', $newMoney->currency);
    }

    #[Test]
    public function add_preserves_currency(): void
    {
        $money = new Money('100.00', 'USD');

        $newMoney = $money->add('50.00');

        $this->assertSame('USD', $newMoney->currency);
    }

    // ===== GET_VALUE METHOD TESTS =====

    #[Test]
    public function get_value_returns_float_amount(): void
    {
        $money = new Money('100.50', 'UAH');

        $this->assertSame(100.5, $money->getValue());
    }

    // ===== EQUALS METHOD TESTS =====

    #[Test]
    public function equals_returns_true_when_same_amount_and_currency(): void
    {
        $money1 = new Money('100.00', 'UAH');
        $money2 = new Money('100.00', 'UAH');

        $this->assertTrue($money1->equals($money2));
    }

    #[Test]
    public function equals_returns_false_when_different_amount(): void
    {
        $money1 = new Money('100.00', 'UAH');
        $money2 = new Money('200.00', 'UAH');

        $this->assertFalse($money1->equals($money2));
    }

    #[Test]
    public function equals_returns_false_when_different_currency(): void
    {
        $money1 = new Money('100.00', 'UAH');
        $money2 = new Money('100.00', 'USD');

        $this->assertFalse($money1->equals($money2));
    }
}
