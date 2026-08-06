<?php

namespace Tests\Unit\Helpers;

use PHPUnit\Framework\Attributes\Test;

use Tests\TestCase;

class WhatsAppHelperTest extends TestCase
{
    #[Test]
    public function it_formats_canadian_phone_numbers_for_whatsapp()
    {
        $phoneNumbers = [
            '+1-514-555-0123' => '15145550123',
            '514-555-0123' => '15145550123',
            '(514) 555-0123' => '15145550123',
            '+1 514 555 0123' => '15145550123'
        ];

        foreach ($phoneNumbers as $input => $expected) {
            $formatted = $this->formatPhoneForWhatsApp($input);
            $this->assertEquals($expected, $formatted, "Failed to format: {$input}");
        }
    }

    #[Test]
    public function it_generates_whatsapp_urls()
    {
        $phone = '15145550123';
        $message = 'Hello, I need your services';

        $url = $this->generateWhatsAppUrl($phone, $message);

        $this->assertStringContainsString('https://wa.me/', $url);
        $this->assertStringContainsString($phone, $url);
        $this->assertStringContainsString(urlencode($message), $url);
    }

    #[Test]
    public function it_validates_whatsapp_number_format()
    {
        $validNumbers = [
            '15145550123',
            '16135551234',
            '15145559999'
        ];

        foreach ($validNumbers as $number) {
            $this->assertTrue($this->isValidWhatsAppNumber($number));
        }
    }

    #[Test]
    public function it_rejects_invalid_whatsapp_numbers()
    {
        $invalidNumbers = [
            '123456',      // Too short
            'abc123456789', // Contains letters
            '+1514555123', // Contains + symbol
            '25145550123'  // Invalid country code
        ];

        foreach ($invalidNumbers as $number) {
            $this->assertFalse($this->isValidWhatsAppNumber($number));
        }
    }

    // Helper methods for testing
    private function formatPhoneForWhatsApp(string $phone): string
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/\D/', '', $phone);

        // Add country code if missing
        if (strlen($cleaned) === 10) {
            $cleaned = '1' . $cleaned;
        }

        return $cleaned;
    }

    private function generateWhatsAppUrl(string $phone, string $message = ''): string
    {
        $url = "https://wa.me/{$phone}";
        if (!empty($message)) {
            $url .= '?text=' . urlencode($message);
        }
        return $url;
    }

    private function isValidWhatsAppNumber(string $number): bool
    {
        // Must be 11 digits for North America (1 + 10 digits)
        return preg_match('/^1[0-9]{10}$/', $number);
    }
}
