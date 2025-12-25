<?php

namespace Tests\Unit\Rules;

use Tests\TestCase;
use Illuminate\Support\Facades\Validator;

class LocationValidationTest extends TestCase
{
    /** @test */
    public function it_validates_canadian_cities()
    {
        $validCities = ['Laval', 'Montreal', 'Ottawa', 'Gatineau'];

        foreach ($validCities as $city) {
            $validator = Validator::make(['city' => $city], [
                'city' => 'required|in:Laval,Montreal,Ottawa,Gatineau'
            ]);

            $this->assertTrue($validator->passes(), "City {$city} should be valid");
        }
    }

    /** @test */
    public function it_rejects_non_canadian_cities()
    {
        $invalidCities = ['Toronto', 'Vancouver', 'New York', 'Paris', 'London'];

        foreach ($invalidCities as $city) {
            $validator = Validator::make(['city' => $city], [
                'city' => 'required|in:Laval,Montreal,Ottawa,Gatineau'
            ]);

            $this->assertTrue($validator->fails(), "City {$city} should be invalid");
        }
    }

    /** @test */
    public function it_validates_canadian_provinces()
    {
        $validProvinces = ['Quebec', 'Ontario'];

        foreach ($validProvinces as $province) {
            $validator = Validator::make(['province' => $province], [
                'province' => 'required|in:Quebec,Ontario'
            ]);

            $this->assertTrue($validator->passes(), "Province {$province} should be valid");
        }
    }
}
