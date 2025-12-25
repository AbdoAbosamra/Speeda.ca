<?php

namespace Tests\Unit\Rules;

use Tests\TestCase;
use Illuminate\Support\Facades\Validator;

class EmailValidationTest extends TestCase
{
    /** @test */
    public function it_validates_correct_email_formats()
    {
        $validEmails = [
            'user@example.com',
            'test.email@domain.ca',
            'contact@homeservices.com',
            'admin+test@site.org'
        ];

        foreach ($validEmails as $email) {
            $validator = Validator::make(['email' => $email], [
                'email' => 'required|email'
            ]);

            $this->assertTrue($validator->passes(), "Email {$email} should be valid");
        }
    }

    /** @test */
    public function it_rejects_invalid_email_formats()
    {
        $invalidEmails = [
            'invalid-email',
            '@domain.com',
            'user@',
            'user..name@domain.com',
            'user@domain..com'
        ];

        foreach ($invalidEmails as $email) {
            $validator = Validator::make(['email' => $email], [
                'email' => 'required|email'
            ]);

            $this->assertTrue($validator->fails(), "Email {$email} should be invalid");
        }
    }

    /** @test */
    public function it_validates_unique_email_addresses()
    {
        // This would typically test against database uniqueness
        $validator = Validator::make(['email' => 'test@example.com'], [
            'email' => 'required|email|unique:users,email'
        ]);

        // Should pass when no user exists with this email
        $this->assertTrue($validator->passes());
    }
}
