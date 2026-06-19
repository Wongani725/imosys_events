<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Helpers\MealCalculator;

class MealCalculatorTest extends TestCase
{
    public function test_governance_without_accommodation_returns_2_meals()
    {
        $this->assertEquals(2, MealCalculator::calculate('governance', false));
    }

    public function test_governance_with_accommodation_returns_5_meals()
    {
        $this->assertEquals(5, MealCalculator::calculate('governance', true));
    }

    public function test_main_without_accommodation_returns_2_meals()
    {
        $this->assertEquals(2, MealCalculator::calculate('main', false));
    }

    public function test_main_with_accommodation_returns_5_meals()
    {
        $this->assertEquals(5, MealCalculator::calculate('main', true));
    }

    public function test_both_events_with_accommodation_returns_6_for_governance()
    {
        $this->assertEquals(6, MealCalculator::calculate('governance', true, true));
    }

    public function test_both_events_with_accommodation_returns_5_for_main()
    {
        $this->assertEquals(5, MealCalculator::calculate('main', true, true));
    }

    public function test_breakdown_governance_no_accommodation()
    {
        $result = MealCalculator::breakdown('governance', false);
        $this->assertEquals(['Sep 8 Lunch', 'Sep 9 Lunch', 'total'], array_keys($result));
        $this->assertEquals(2, $result['total']);
    }

    public function test_breakdown_governance_with_accommodation()
    {
        $result = MealCalculator::breakdown('governance', true);
        $this->assertCount(4, $result);
        $this->assertEquals(5, $result['total']);
        $this->assertArrayHasKey('Sep 7 Dinner', $result);
        $this->assertArrayHasKey('Sep 8 Lunch + Dinner', $result);
        $this->assertArrayHasKey('Sep 9 Lunch + Dinner', $result);
    }

    public function test_breakdown_main_no_accommodation()
    {
        $result = MealCalculator::breakdown('main', false);
        $this->assertEquals(['Sep 11 Lunch', 'Sep 12 Lunch', 'total'], array_keys($result));
        $this->assertEquals(2, $result['total']);
    }

    public function test_breakdown_main_with_accommodation()
    {
        $result = MealCalculator::breakdown('main', true);
        $this->assertCount(4, $result);
        $this->assertEquals(5, $result['total']);
        $this->assertArrayHasKey('Sep 10 Dinner', $result);
    }

    public function test_breakdown_both_governance_with_accommodation()
    {
        $result = MealCalculator::breakdown('governance', true, true);
        $this->assertEquals(6, $result['total']);
        $this->assertArrayHasKey('Sep 10 Lunch', $result);
    }

    public function test_breakdown_both_main_with_accommodation()
    {
        $result = MealCalculator::breakdown('main', true, true);
        $this->assertEquals(5, $result['total']);
        $this->assertArrayHasKey('Sep 10 Dinner', $result);
    }
}
