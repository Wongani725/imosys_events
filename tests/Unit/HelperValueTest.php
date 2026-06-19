<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Helpers\Helper;

class HelperValueTest extends TestCase
{
    public function test_number_to_words_zero()
    {
        $this->assertEquals('Zero', Helper::numberToWords(0));
    }

    public function test_number_to_words_single_digit()
    {
        $this->assertEquals('Five', Helper::numberToWords(5));
    }

    public function test_number_to_words_teens()
    {
        $this->assertEquals('Eleven', Helper::numberToWords(11));
        $this->assertEquals('Nineteen', Helper::numberToWords(19));
    }

    public function test_number_to_words_tens()
    {
        $this->assertEquals('Twenty', Helper::numberToWords(20));
        $this->assertEquals('Ninety Nine', Helper::numberToWords(99));
    }

    public function test_number_to_words_hundreds()
    {
        $this->assertEquals('One Hundred', Helper::numberToWords(100));
        $this->assertEquals('Two Hundred Thirty Four', Helper::numberToWords(234));
    }

    public function test_number_to_words_thousands()
    {
        $this->assertEquals('One Thousand', Helper::numberToWords(1000));
        $this->assertEquals('Twelve Thousand Three Hundred Forty Five', Helper::numberToWords(12345));
    }

    public function test_number_to_words_millions()
    {
        $result = Helper::numberToWords(1000000);
        $this->assertStringContainsString('Million', $result);
    }

    public function test_is_input_checked_on()
    {
        $this->assertTrue(Helper::IsInputChecked('on'));
    }

    public function test_is_input_checked_empty()
    {
        $this->assertFalse(Helper::IsInputChecked(''));
        $this->assertFalse(Helper::IsInputChecked(null));
    }

    public function test_is_input_checked_arbitrary_value()
    {
        $this->assertFalse(Helper::IsInputChecked('yes'));
    }

    public function test_is_input_checked_custom_true_false()
    {
        $this->assertEquals('yes', Helper::IsInputChecked('on', 'yes', 'no'));
        $this->assertEquals('no', Helper::IsInputChecked('', 'yes', 'no'));
    }
}
