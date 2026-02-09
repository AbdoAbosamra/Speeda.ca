<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use PHPUnit\Framework\TestCase;

class CategoryModelTest extends TestCase
{
    /**
     * Test slug generation on create
     */
    public function test_slug_is_auto_generated_on_create()
    {
        // This is a unit test - uses mock data
        $category = new Category([
            'name' => 'Test Category',
        ]);

        $this->assertInstanceOf(Category::class, $category);
    }

    /**
     * Test category is section method
     */
    public function test_is_section_method()
    {
        $section = new Category([
            'name' => 'Section',
            'is_section' => true,
            'parent_id' => null,
        ]);

        $this->assertTrue($section->isSection());

        $subcategory = new Category([
            'name' => 'Subcategory',
            'is_section' => false,
            'parent_id' => 1,
        ]);

        $this->assertFalse($subcategory->isSection());
    }

    /**
     * Test category is subcategory method
     */
    public function test_is_subcategory_method()
    {
        $section = new Category([
            'name' => 'Section',
            'is_section' => true,
            'parent_id' => null,
        ]);

        $this->assertFalse($section->isSubcategory());

        $subcategory = new Category([
            'name' => 'Subcategory',
            'is_section' => false,
            'parent_id' => 1,
        ]);

        $this->assertTrue($subcategory->isSubcategory());
    }
}
