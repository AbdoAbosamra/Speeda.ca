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

        // Create an admin user
        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    /**
     * Test admin can view categories list
     */
    public function test_admin_can_view_categories_list()
    {
        $this->actingAs($this->admin)
            ->get(route('admin.categories'))
            ->assertStatus(200)
            ->assertViewIs('admin.categories.index');
    }

    /**
     * Test non-admin cannot view categories list
     */
    public function test_non_admin_cannot_view_categories_list()
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => 'client']);

        $this->actingAs($user)
            ->get(route('admin.categories'))
            ->assertRedirect(route('dashboard'));
    }

    /**
     * Test admin can create a category
     */
    public function test_admin_can_create_category()
    {
        $data = [
            'name' => 'Test Category',
            'description' => 'Test Description',
            'is_active' => true,
            'is_section' => false,
            'sort_order' => 1,
        ];

        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), $data)
            ->assertRedirect(route('admin.categories'));

        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test Description',
            'is_active' => true,
        ]);
    }

    /**
     * Test category slug is auto-generated
     */
    public function test_category_slug_is_auto_generated()
    {
        $data = [
            'name' => 'Cars & Mechanics',
            'is_active' => true,
        ];

        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), $data);

        $this->assertDatabaseHas('categories', [
            'name' => 'Cars & Mechanics',
            'slug' => 'cars-mechanics',
        ]);
    }

    /**
     * Test admin can update a category
     */
    public function test_admin_can_update_category()
    {
        $category = Category::factory()->create();

        $data = [
            'name' => 'Updated Category',
            'description' => 'Updated Description',
            'is_active' => false,
        ];

        $this->actingAs($this->admin)
            ->put(route('admin.categories.update', $category), $data)
            ->assertRedirect(route('admin.categories'));

        $category->refresh();
        $this->assertEquals('Updated Category', $category->name);
        $this->assertEquals('Updated Description', $category->description);
        $this->assertFalse($category->is_active);
    }

    /**
     * Test admin cannot set category parent to itself
     */
    public function test_admin_cannot_set_category_parent_to_itself()
    {
        $category = Category::factory()->create();

        $data = [
            'name' => 'Updated Category',
            'parent_id' => $category->id,
        ];

        $this->actingAs($this->admin)
            ->put(route('admin.categories.update', $category), $data)
            ->assertSessionHasErrors();
    }

    /**
     * Test admin can delete category with no children
     */
    public function test_admin_can_delete_category_with_no_children()
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.categories.delete', $category))
            ->assertRedirect(route('admin.categories'));

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    /**
     * Test admin cannot delete category with children
     */
    public function test_admin_cannot_delete_category_with_children()
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $parent->id]);

        $this->actingAs($this->admin)
            ->delete(route('admin.categories.delete', $parent))
            ->assertSessionHasErrors();

        $this->assertDatabaseHas('categories', ['id' => $parent->id]);
    }

    /**
     * Test category name is required
     */
    public function test_category_name_is_required()
    {
        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), [])
            ->assertSessionHasErrors('name');
    }

    /**
     * Test category slug validation
     */
    public function test_category_slug_validation()
    {
        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Test Category',
                'slug' => 'invalid slug with spaces',
            ])
            ->assertSessionHasErrors('slug');
    }
}
