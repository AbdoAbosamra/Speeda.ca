<?php

namespace Tests\Unit\Rules;

use App\Rules\BusinessHoursValidation;
use Tests\TestCase;
use Illuminate\Support\Facades\Validator;

class BusinessHoursValidationTest extends TestCase
{
    /** @test */
    public function it_validates_correct_business_hours_format()
    {
        $validHours = [
            '09:00-17:00',
            '08:30-18:30',
            '10:00-16:00',
            '00:00-23:59'
        ];

        foreach ($validHours as $hours) {
            $validator = Validator::make(['hours' => $hours], [
                'hours' => ['required', new BusinessHoursValidation()]
            ]);

            $this->assertTrue($validator->passes(), "Hours format {$hours} should be valid");
        }
    }

    /** @test */
    public function it_rejects_invalid_business_hours_format()
    {
        $invalidHours = [
            '25:00-17:00',  // Invalid hour
            '09:60-17:00',  // Invalid minute
            '09:00 17:00',  // Missing dash
            '17:00-09:00',  // End before start
            '12:30-12:30'   // Same start and end time
        ];

        foreach ($invalidHours as $hours) {
            $validator = Validator::make(['hours' => $hours], [
                'hours' => ['required', new BusinessHoursValidation()]
            ]);

            $this->assertTrue($validator->fails(), "Hours format {$hours} should be invalid");
        }
    }

    /** @test */
    public function it_validates_weekly_schedule_array()
    {
        $validSchedule = [
            'monday' => '09:00-17:00',
            'tuesday' => '09:00-17:00',
            'wednesday' => '09:00-17:00',
            'thursday' => '09:00-17:00',
            'friday' => '09:00-17:00',
            'saturday' => '10:00-14:00',
            'sunday' => 'closed'
        ];

        // Test each day individually
        foreach ($validSchedule as $day => $hours) {
            if ($hours !== 'closed') {
                $validator = Validator::make([$day => $hours], [
                    $day => ['required', new BusinessHoursValidation()]
                ]);
                $this->assertTrue($validator->passes(), "Valid hours for {$day}: {$hours}");
            }
        }

        // Test the full schedule structure
        $validator = Validator::make(['schedule' => $validSchedule], [
            'schedule' => 'required|array',
            'schedule.monday' => 'required|string',
            'schedule.tuesday' => 'required|string',
            'schedule.wednesday' => 'required|string',
            'schedule.thursday' => 'required|string',
            'schedule.friday' => 'required|string',
            'schedule.saturday' => 'required|string',
            'schedule.sunday' => 'required|string'
        ]);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function it_handles_closed_days_properly()
    {
        $scheduleWithClosedDays = [
            'monday' => '09:00-17:00',
            'tuesday' => 'closed',
            'wednesday' => '09:00-17:00',
            'thursday' => 'closed',
            'friday' => '09:00-17:00',
            'saturday' => 'closed',
            'sunday' => 'closed'
        ];

        $this->assertContains('closed', $scheduleWithClosedDays);
        $this->assertEquals('closed', $scheduleWithClosedDays['tuesday']);
        $this->assertEquals('closed', $scheduleWithClosedDays['sunday']);
    }
}
