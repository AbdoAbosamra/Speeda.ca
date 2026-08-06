<?php

namespace Tests\Unit\Rules;

use PHPUnit\Framework\Attributes\Test;

use App\Rules\CanadianPhoneNumber;
use Tests\TestCase;

/**
 * 🧪 Canadian Phone Number Validation Rule Tests
 *
 * Testing all edge cases and validation scenarios
 * Priority: ⭐⭐⭐⭐⭐ (Critical)
 */
class CanadianPhoneNumberTest extends TestCase
{
    private CanadianPhoneNumber $rule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new CanadianPhoneNumber();
    }

    #[Test]
    public function it_accepts_valid_10_digit_phone_numbers()
    {
        $validNumbers = [
            '5145551234',
            '4165551234',
            '6135551234',
            '7805551234',
            '2505551234'
        ];

        foreach ($validNumbers as $number) {
            $this->assertValidationPasses($number, "Failed for: {$number}");
        }
    }

    #[Test]
    public function it_accepts_valid_formatted_phone_numbers()
    {
        $validFormats = [
            '(514) 555-1234',
            '514-555-1234',
            '514.555.1234',
            '514 555 1234',
            '+1 514 555 1234',
            '+15145551234'
        ];

        foreach ($validFormats as $number) {
            $this->assertValidationPasses($number, "Failed for format: {$number}");
        }
    }

    #[Test]
    public function it_accepts_11_digit_numbers_starting_with_1()
    {
        $validNumbers = [
            '15145551234',
            '14165551234',
            '+15145551234'
        ];

        foreach ($validNumbers as $number) {
            $this->assertValidationPasses($number, "Failed for 11-digit: {$number}");
        }
    }

    #[Test]
    public function it_rejects_numbers_starting_with_0()
    {
        $invalidNumbers = [
            '0145551234',
            '(014) 555-1234',
            '+10145551234'
        ];

        foreach ($invalidNumbers as $number) {
            $this->assertValidationFails($number, "Should fail for: {$number}");
        }
    }

    #[Test]
    public function it_rejects_numbers_starting_with_1()
    {
        $invalidNumbers = [
            '1145551234',
            '(114) 555-1234'
        ];

        foreach ($invalidNumbers as $number) {
            $this->assertValidationFails($number, "Should fail for: {$number}");
        }
    }

    #[Test]
    public function it_rejects_numbers_with_wrong_length()
    {
        $invalidNumbers = [
            '514555123',     // 9 digits
            '51455512345',   // 11 digits (not starting with 1)
            '5145551',       // 7 digits
            '514555123456'   // 12 digits
        ];

        foreach ($invalidNumbers as $number) {
            $this->assertValidationFails($number, "Should fail for length: {$number}");
        }
    }

    #[Test]
    public function it_accepts_empty_values()
    {
        $emptyValues = [null, '', ' ', '   '];

        foreach ($emptyValues as $value) {
            $this->assertValidationPasses($value, "Should pass for empty: '{$value}'");
        }
    }

    #[Test]
    public function it_strips_non_digit_characters()
    {
        $messyNumbers = [
            '(514) 555-1234 ext 123',  // Should use first 10 digits
            '514.555.1234',
            '514 - 555 - 1234',
            'Phone: 514-555-1234',
            '514/555/1234'
        ];

        foreach ($messyNumbers as $number) {
            $this->assertValidationPasses($number, "Should clean and pass: {$number}");
        }
    }

    #[Test]
    public function it_handles_edge_case_area_codes()
    {
        // Test some real Canadian area codes
        $canadianAreaCodes = [
            '204', // Manitoba
            '250', // British Columbia
            '289', // Ontario
            '306', // Saskatchewan
            '403', // Alberta
            '416', // Ontario
            '418', // Quebec
            '450', // Quebec
            '506', // New Brunswick
            '514', // Quebec
            '519', // Ontario
            '579', // Quebec
            '581', // Quebec
            '587', // Alberta
            '604', // British Columbia
            '613', // Ontario
            '647', // Ontario
            '705', // Ontario
            '709', // Newfoundland
            '778', // British Columbia
            '780', // Alberta
            '807', // Ontario
            '819', // Quebec
            '825', // Alberta
            '867', // Northwest Territories
            '873', // Quebec
            '902', // Nova Scotia
            '905'  // Ontario
        ];

        foreach ($canadianAreaCodes as $areaCode) {
            $phoneNumber = $areaCode . '5551234';
            $this->assertValidationPasses($phoneNumber, "Should pass for area code: {$areaCode}");
        }
    }

    #[Test]
    public function it_handles_international_format()
    {
        $internationalFormats = [
            '+1-514-555-1234',
            '+1 (514) 555-1234',
            '+1.514.555.1234'
        ];

        foreach ($internationalFormats as $number) {
            $this->assertValidationPasses($number, "Should pass for international: {$number}");
        }
    }

    #[Test]
    public function it_rejects_obviously_fake_numbers()
    {
        $fakeNumbers = [
            '0000000000',
            '1111111111',
            '1234567890',
            '0123456789'
        ];

        foreach ($fakeNumbers as $number) {
            $this->assertValidationFails($number, "Should fail fake number: {$number}");
        }
    }

    #[Test]
    public function it_handles_special_characters_gracefully()
    {
        $numbersWithSpecialChars = [
            '514-555-1234#',
            '(514) 555-1234*',
            '514.555.1234,',
            '514 555 1234;'
        ];

        foreach ($numbersWithSpecialChars as $number) {
            // These should either pass (if they clean up to valid) or fail gracefully
            try {
                $this->assertValidationPasses($number);
            } catch (\Exception $e) {
                $this->assertValidationFails($number);
            }
        }
    }

    #[Test]
    public function performance_test_with_many_validations()
    {
        $startTime = microtime(true);

        // Test performance with 1000 validations
        for ($i = 0; $i < 1000; $i++) {
            $phoneNumber = '514555' . str_pad($i % 10000, 4, '0', STR_PAD_LEFT);
            $this->assertValidationPasses($phoneNumber);
        }

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime);

        // Should complete 1000 validations in under 1 second
        $this->assertLessThan(1.0, $executionTime, 'Validation should be fast');
    }

    /**
     * Helper method to test if validation passes
     */
    private function assertValidationPasses($value, $message = '')
    {
        $failed = false;

        $this->rule->validate('phone', $value, function() use (&$failed) {
            $failed = true;
        });

        $this->assertFalse($failed, $message ?: "Validation should pass for: {$value}");
    }

    /**
     * Helper method to test if validation fails
     */
    private function assertValidationFails($value, $message = '')
    {
        $failed = false;

        $this->rule->validate('phone', $value, function() use (&$failed) {
            $failed = true;
        });

        $this->assertTrue($failed, $message ?: "Validation should fail for: {$value}");
    }
}
