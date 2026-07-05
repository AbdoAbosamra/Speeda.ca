<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoriesTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    /** Test admin can view categories list */
    public function test_admin_can_view_categories_list()
    {
        $this->actingAs($this->admin)
            ->get(route('admin.categories'))
            ->assertStatus(200)
            ->assertViewIs('admin.categories.index');
    }

    /** Test non-admin cannot view categories list */
    public function test_non_admin_cannot_view_categories_list()
    {
        $user = User::factory()->client()->create();

        $this->actingAs($user)
            ->get(route('admin.categories'))
            ->assertForbidden();
    }

    /** Test admin can create a category (multi-language) */
    public function test_admin_can_create_category()
    {
        $data = [
            'name_ar' => 'تصنيف تجريبي',
            'name_en' => 'Test Category',
            'color' => '#dc3545',
            'is_active' => true,
            'is_section' => false,
            'sort_order' => 1,
        ];

        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), $data)
            ->assertRedirect(route('admin.categories'));

        $this->assertDatabaseHas('categories', [
            'name_en' => 'Test Category',
            'slug' => 'test-category',
        ]);
    }

    /** Test category slug is auto-generated from the English name */
    public function test_category_slug_is_auto_generated()
    {
        $data = [
            'name_ar' => 'سيارات وميكانيكا',
            'name_en' => 'Cars & Mechanics',
            'color' => '#dc3545',
            'is_active' => true,
        ];

        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), $data);

        $this->assertDatabaseHas('categories', [
            'name_en' => 'Cars & Mechanics',
            'slug' => 'cars-mechanics',
        ]);
    }

    /** Test admin can update a category */
    public function test_admin_can_update_category()
    {
        $category = Category::factory()->create();

        $data = [
            'name_ar' => 'تصنيف محدث',
            'name_en' => 'Updated Category',
            'color' => '#dc3545',
            'is_active' => false,
        ];

        $this->actingAs($this->admin)
            ->patch(route('admin.categories.update', $category), $data)
            ->assertRedirect(route('admin.categories'));

        $category->refresh();
        $this->assertEquals('Updated Category', $category->name_en);
        $this->assertFalse((bool) $category->is_active);
    }

    /** Test admin cannot set category parent to itself */
    public function test_admin_cannot_set_category_parent_to_itself()
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.categories.update', $category), [
                'name_ar' => 'تصنيف',
                'parent_id' => $category->id,
            ])
            ->assertSessionHasErrors('parent_id');
    }

    /** Test admin can delete an inactive category with no children (soft delete) */
    public function test_admin_can_delete_category_with_no_children()
    {
        $category = Category::factory()->create(['is_active' => false]);

        $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories'));

        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    /** Test admin cannot delete a category that has children */
    public function test_admin_cannot_delete_category_with_children()
    {
        $parent = Category::factory()->create(['is_active' => false]);
        Category::factory()->create(['parent_id' => $parent->id, 'is_active' => false]);

        $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $parent))
            ->assertRedirect();

        $this->assertDatabaseHas('categories', ['id' => $parent->id, 'deleted_at' => null]);
    }

    /** Test category name is required */
    public function test_category_name_is_required()
    {
        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), [])
            ->assertSessionHasErrors('name_ar');
    }
}
