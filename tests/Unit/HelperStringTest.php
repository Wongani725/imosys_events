<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Helpers\Helper;

class HelperStringTest extends TestCase
{
    public function test_contains_word_exact_match()
    {
        $this->assertTrue(Helper::ContainsWord('hello world', 'world'));
    }

    public function test_contains_word_case_insensitive()
    {
        $this->assertTrue(Helper::ContainsWord('Hello World', 'world'));
    }

    public function test_contains_word_no_match()
    {
        $this->assertFalse(Helper::ContainsWord('hello world', 'foo'));
    }

    public function test_contains_word_partial_no_match()
    {
        $this->assertFalse(Helper::ContainsWord('hello', 'he'));
    }

    public function test_starts_with_true()
    {
        $this->assertTrue(Helper::StartsWith('hello world', 'hello'));
    }

    public function test_starts_with_false()
    {
        $this->assertFalse(Helper::StartsWith('hello world', 'world'));
    }

    public function test_ends_with_true()
    {
        $this->assertTrue(Helper::EndsWith('hello world', 'world'));
    }

    public function test_ends_with_false()
    {
        $this->assertFalse(Helper::EndsWith('hello world', 'hello'));
    }

    public function test_has_prefix_true()
    {
        $this->assertTrue(Helper::HasPrefix('IIA-GF-2026', 'IIA-'));
    }

    public function test_has_prefix_false()
    {
        $this->assertFalse(Helper::HasPrefix('GF-2026', 'IIA-'));
    }

    public function test_ordinal_suffix_st()
    {
        $this->assertStringContainsString('1st', Helper::OrdinalSuffix(1));
    }

    public function test_ordinal_suffix_nd()
    {
        $this->assertStringContainsString('2nd', Helper::OrdinalSuffix(2));
    }

    public function test_ordinal_suffix_rd()
    {
        $this->assertStringContainsString('3rd', Helper::OrdinalSuffix(3));
    }

    public function test_ordinal_suffix_th()
    {
        $this->assertStringContainsString('11th', Helper::OrdinalSuffix(11));
        $this->assertStringContainsString('12th', Helper::OrdinalSuffix(12));
        $this->assertStringContainsString('13th', Helper::OrdinalSuffix(13));
    }

    public function test_ordinal_suffix_21st()
    {
        $this->assertStringContainsString('21st', Helper::OrdinalSuffix(21));
    }

    public function test_prefix_pads_with_zeros()
    {
        $this->assertEquals('0042', Helper::Prefix(42));
    }

    public function test_prefix_custom_count()
    {
        $this->assertEquals('00042', Helper::Prefix(42, '0', 5));
    }
}
