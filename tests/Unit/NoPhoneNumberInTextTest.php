<?php

namespace Tests\Unit;

use App\Rules\NoPhoneNumberInText;
use PHPUnit\Framework\TestCase;

class NoPhoneNumberInTextTest extends TestCase
{
    public function test_detects_local_mobile_formats(): void
    {
        $this->assertTrue(NoPhoneNumberInText::containsPhoneNumber('Nipigie 0712345678'));
        $this->assertTrue(NoPhoneNumberInText::containsPhoneNumber('06 12 345 678'));
        $this->assertTrue(NoPhoneNumberInText::containsPhoneNumber('07-1234-5678'));
    }

    public function test_detects_international_formats(): void
    {
        $this->assertTrue(NoPhoneNumberInText::containsPhoneNumber('+255 712 345 678'));
        $this->assertTrue(NoPhoneNumberInText::containsPhoneNumber('255712345678'));
        $this->assertTrue(NoPhoneNumberInText::containsPhoneNumber('255 6 123 456 78'));
    }

    public function test_detects_partial_mobile_numbers(): void
    {
        $this->assertTrue(NoPhoneNumberInText::containsPhoneNumber('0786432'));
        $this->assertTrue(NoPhoneNumberInText::containsPhoneNumber('kazi zipo 0786432'));
        $this->assertTrue(NoPhoneNumberInText::containsPhoneNumber('07 864 32'));
    }

    public function test_detects_numbers_without_leading_zero(): void
    {
        $this->assertTrue(NoPhoneNumberInText::containsPhoneNumber('750599412'));
        $this->assertTrue(NoPhoneNumberInText::containsPhoneNumber('650599412'));
        $this->assertTrue(NoPhoneNumberInText::containsPhoneNumber('nipigie 750599412'));
        $this->assertTrue(NoPhoneNumberInText::containsPhoneNumber('7 505 994 12'));
        $this->assertTrue(NoPhoneNumberInText::containsPhoneNumber('6 505 994 12'));
    }

    public function test_allows_normal_job_text(): void
    {
        $this->assertFalse(NoPhoneNumberInText::containsPhoneNumber('Nahitaji usafi wa nyumba ya vyumba 3.'));
        $this->assertFalse(NoPhoneNumberInText::containsPhoneNumber('Bei ni TZS 15000 kwa siku.'));
        $this->assertFalse(NoPhoneNumberInText::containsPhoneNumber(''));
    }
}
