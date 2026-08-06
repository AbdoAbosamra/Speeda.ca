<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\Attributes\Test;

use App\Models\Category;
use App\Models\ServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_be_created_with_valid_data()
    {
        $category = Category::factory()->create([
            'name' => 'Home Services',
            'description' => 'Professional home services',
            'slug' => 'home-services',
            'is_active' => true
        ]);

        $this->assertInstanceOf(Category::class, $category);
        $this->assertEquals('Home Services', $category->name);
        $this->assertEquals('Professional home services', $category->description);
        $this->assertEquals('home-services', $category->slug);
        $this->assertTrue($category->is_active);
        $this->assertDatabaseHas('categories', [
            'name' => 'Home Services'
        ]);
    }

    #[Test]
    public function it_has_required_attributes()
    {
        $category = Category::factory()->create([
            'name' => 'Cleaning Services',
            'slug' => 'cleaning-services',
            'description' => 'Professional cleaning services',
            'icon' => '🧹',
            'color' => '#28a745'
        ]);

        $this->assertEquals('Cleaning Services', $category->name);
        $this->assertStringContainsString('cleaning-services', $category->slug);
        $this->assertEquals('Professional cleaning services', $category->description);
        $this->assertEquals('🧹', $category->icon);
        $this->assertEquals('#28a745', $category->color);
    }

    #[Test]
    public function it_can_have_parent_child_relationships()
    {
        $parentCategory = Category::factory()->create(['name' => 'Home Services']);
        $childCategory = Category::factory()->create([
            'name' => 'Plumbing',
            'parent_id' => $parentCategory->id
        ]);

        $this->assertEquals($parentCategory->id, $childCategory->parent_id);
        $this->assertTrue($childCategory->parent->is($parentCategory));
        $this->assertTrue($parentCategory->children->contains($childCategory));
    }

    #[Test]
    public function it_can_have_many_service_providers()
    {
        $category = Category::factory()->create();
        $serviceProvider1 = ServiceProvider::factory()->create(['category_id' => $category->id]);
        $serviceProvider2 = ServiceProvider::factory()->create(['category_id' => $category->id]);

        $this->assertCount(2, $category->serviceProviders);
        $this->assertTrue($category->serviceProviders->contains($serviceProvider1));
        $this->assertTrue($category->serviceProviders->contains($serviceProvider2));
    }

    #[Test]
    public function it_uses_soft_deletes()
    {
        $category = Category::factory()->create(['name' => 'Test Category']);
        $categoryId = $category->id;

        $category->delete();

        $this->assertSoftDeleted('categories', ['id' => $categoryId]);
        $this->assertNull(Category::find($categoryId));
        $this->assertNotNull(Category::withTrashed()->find($categoryId));
    }

    #[Test]
    public function it_can_be_restored_after_soft_delete()
    {
        $category = Category::factory()->create(['name' => 'Test Category']);
        $categoryId = $category->id;

        $category->delete();
        $this->assertSoftDeleted('categories', ['id' => $categoryId]);

        Category::withTrashed()->find($categoryId)->restore();
        $this->assertNotNull(Category::find($categoryId));
        $this->assertDatabaseHas('categories', [
            'id' => $categoryId,
            'deleted_at' => null
        ]);
    }

    #[Test]
    public function factory_creates_valid_categories()
    {
        $categories = Category::factory()->count(5)->create();

        $this->assertCount(5, $categories);

        foreach ($categories as $category) {
            $this->assertNotNull($category->name);
            $this->assertNotNull($category->slug);
            $this->assertIsString($category->name);
            $this->assertIsString($category->slug);
            $this->assertIsBool($category->is_active);
            $this->assertIsInt($category->sort_order);
        }
    }

    #[Test]
    public function it_requires_name_field()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Category::create([
            'name' => null,
            'slug' => 'test'
        ]);
    }

    #[Test]
    public function it_can_be_marked_as_section()
    {
        $category = Category::factory()->create([
            'name' => 'Service Sections',
            'is_section' => true
        ]);

        $this->assertTrue($category->is_section);
    }
}
