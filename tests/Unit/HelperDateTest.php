<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Helpers\Helper;

class HelperDateTest extends TestCase
{
    public function test_date_interval_returns_old_minus_new()
    {
        $this->assertEquals(-5, Helper::DateInterval('2025-01-10', '2025-01-05'));
    }

    public function test_date_interval_returns_positive_when_old_is_after_new()
    {
        $this->assertEquals(5, Helper::DateInterval('2025-01-05', '2025-01-10'));
    }

    public function test_date_interval_absolute()
    {
        $this->assertEquals(5, Helper::DateInterval('2025-01-05', '2025-01-10', true));
    }

    public function test_date_interval_zero()
    {
        $this->assertEquals(0, Helper::DateInterval('2025-01-10', '2025-01-10'));
    }

    public function test_days_between()
    {
        $this->assertEquals(5, Helper::DaysBetween('2025-01-05', '2025-01-10'));
    }

    public function test_months_between()
    {
        $this->assertEquals(2, Helper::MonthsBetween('2025-01-05', '2025-03-10'));
    }

    public function test_years_between()
    {
        $this->assertEquals(2, Helper::YearsBetween('2023-01-05', '2025-01-10'));
    }

    public function test_add_days_to_date()
    {
        $this->assertEquals('2025-01-15', Helper::AddDaysToDate('2025-01-10', 5));
    }

    public function test_subtract_days_from_date()
    {
        $this->assertEquals('2025-01-05', Helper::SubtractDaysFromDate('2025-01-10', 5));
    }

    public function test_date_is_not_after_today()
    {
        $this->assertTrue(Helper::DateIsNotAfterToday('2020-01-01'));
        $this->assertFalse(Helper::DateIsNotAfterToday('2099-01-01'));
    }

    public function test_first_date_of_current_month()
    {
        $expected = date('Y-m-01');
        $this->assertEquals($expected, Helper::FirstDateOfCurrentMonth());
    }
}
