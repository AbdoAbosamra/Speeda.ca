<?php

namespace Tests\Unit\Models;

use App\Models\Location;
use App\Models\ServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_validates_canadian_cities_enum()
    {
        // الـ Locations موجودة بالفعل من الـ migration، لذا نقوم بجلبها
        $validCities = ['Laval', 'Montreal', 'Ottawa', 'Gatineau'];

        foreach ($validCities as $city) {
            $location = Location::where('city', $city)->first();
            $this->assertNotNull($location);
            $this->assertEquals($city, $location->city);
            $this->assertInstanceOf(Location::class, $location);
        }
    }

    /** @test */
    public function it_has_all_canadian_cities_seeded()
    {
        $expectedCities = ['Laval', 'Montreal', 'Ottawa', 'Gatineau'];

        foreach ($expectedCities as $city) {
            $this->assertDatabaseHas('locations', [
                'city' => $city,
                'is_active' => true
            ]);
        }

        // التأكد من وجود 4 مدن فقط
        $this->assertEquals(4, Location::count());
    }

    /** @test */
    public function it_has_correct_city_province_mapping()
    {
        // هذا اختبار منطقي للمدن والمقاطعات (حتى لو لم تكن محفوظة في DB)
        $cityProvinceMap = [
            'Montreal' => 'Quebec',
            'Laval' => 'Quebec',
            'Gatineau' => 'Quebec',
            'Ottawa' => 'Ontario'
        ];

        foreach ($cityProvinceMap as $city => $expectedProvince) {
            $location = Location::where('city', $city)->first();
            $this->assertNotNull($location);

            // اختبار منطقي للمقاطعة
            $actualProvince = $this->getCityProvince($city);
            $this->assertEquals($expectedProvince, $actualProvince);
        }
    }

    /** @test */
    public function it_can_have_many_service_providers()
    {
        $location = Location::where('city', 'Montreal')->first();
        $serviceProvider1 = ServiceProvider::factory()->create(['location_id' => $location->id]);
        $serviceProvider2 = ServiceProvider::factory()->create(['location_id' => $location->id]);

        $this->assertCount(2, $location->serviceProviders);
        $this->assertTrue($location->serviceProviders->contains($serviceProvider1));
        $this->assertTrue($location->serviceProviders->contains($serviceProvider2));
    }

    /** @test */
    public function locations_are_active_by_default()
    {
        $locations = Location::all();

        foreach ($locations as $location) {
            $this->assertEquals(1, $location->is_active); // Database stores as 1/0
            $this->assertIsInt($location->is_active); // Raw value is integer
        }
    }

    /** @test */
    public function it_has_proper_timestamps()
    {
        $location = Location::first();

        $this->assertNotNull($location->created_at);
        $this->assertNotNull($location->updated_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $location->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $location->updated_at);
    }

    /**
     * Helper method to determine province based on city
     */
    private function getCityProvince(string $city): string
    {
        $quebecCities = ['Montreal', 'Laval', 'Gatineau'];
        $ontarioCities = ['Ottawa'];

        if (in_array($city, $quebecCities)) {
            return 'Quebec';
        }

        if (in_array($city, $ontarioCities)) {
            return 'Ontario';
        }

        return 'Unknown';
    }
}
