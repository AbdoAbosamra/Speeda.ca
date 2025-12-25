<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BusinessHoursValidation implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // First check if the format is correct
        if (!preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]-([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $value)) {
            $fail("The {$attribute} format is invalid. Use HH:MM-HH:MM format.");
            return;
        }

        // Split the time range
        [$start, $end] = explode('-', $value);

        // Convert to minutes for easy comparison
        $startMinutes = $this->timeToMinutes($start);
        $endMinutes = $this->timeToMinutes($end);

        // Check if end time is after start time
        if ($endMinutes <= $startMinutes) {
            $fail("The {$attribute} end time must be after the start time.");
        }
    }

    /**
     * Convert time string to minutes
     */
    private function timeToMinutes(string $time): int
    {
        [$hours, $minutes] = explode(':', $time);
        return (int)$hours * 60 + (int)$minutes;
    }
}
