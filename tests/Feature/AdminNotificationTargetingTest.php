<?php

namespace Tests\Feature;

use App\Models\AdminNotification;
use App\Models\Location;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdminNotificationTargetingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->client()->create([
            'email' => 'notification-admin@example.test',
        ]);

        config(['auth.admins' => [$this->admin->email]]);
    }

    public function test_admin_can_view_provider_targeting_controls(): void
    {
        $provider = $this->createProvider('Target Provider');

        $this->actingAs($this->admin)
            ->get(route('admin.notifications.create'))
            ->assertOk()
            ->assertSee('Selected Service Providers')
            ->assertSee($provider->company_name);
    }

    public function test_admin_can_create_broadcast_notification_for_all_service_providers(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.notifications.store'), $this->payload([
                'target_mode' => 'all',
            ]));

        $response->assertRedirect(route('admin.notifications.index'));

        $notification = AdminNotification::latest()->first();

        $this->assertNotNull($notification);
        $this->assertTrue($notification->isBroadcast());
        $this->assertDatabaseMissing('admin_notification_service_provider', [
            'admin_notification_id' => $notification->id,
        ]);
    }

    public function test_admin_can_create_notification_for_selected_service_providers_only(): void
    {
        $selectedProvider = $this->createProvider('Selected Provider');
        $otherProvider = $this->createProvider('Other Provider');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.notifications.store'), $this->payload([
                'target_mode' => 'selected',
                'service_provider_ids' => [$selectedProvider->id],
            ]));

        $response->assertRedirect(route('admin.notifications.index'));

        $notification = AdminNotification::latest()->first();

        $this->assertFalse($notification->isBroadcast());
        $this->assertDatabaseHas('admin_notification_service_provider', [
            'admin_notification_id' => $notification->id,
            'service_provider_id' => $selectedProvider->id,
        ]);
        $this->assertDatabaseMissing('admin_notification_service_provider', [
            'admin_notification_id' => $notification->id,
            'service_provider_id' => $otherProvider->id,
        ]);
    }

    public function test_admin_cannot_target_inactive_service_provider(): void
    {
        $inactiveProvider = $this->createProvider('Inactive Provider');
        $inactiveProvider->user->update(['is_active' => false]);

        $this->actingAs($this->admin)
            ->from(route('admin.notifications.create'))
            ->post(route('admin.notifications.store'), $this->payload([
                'target_mode' => 'selected',
                'service_provider_ids' => [$inactiveProvider->id],
            ]))
            ->assertRedirect(route('admin.notifications.create'))
            ->assertSessionHasErrors('service_provider_ids');

        $this->assertDatabaseCount('admin_notifications', 0);
    }

    public function test_selected_notification_is_visible_only_to_selected_provider(): void
    {
        $selectedProvider = $this->createProvider('Selected Provider');
        $otherProvider = $this->createProvider('Other Provider');

        $broadcast = AdminNotification::factory()->active()->createdBy($this->admin->id)->create([
            'title_en' => 'Broadcast visible',
        ]);
        $targeted = AdminNotification::factory()->active()->createdBy($this->admin->id)->create([
            'title_en' => 'Selected only visible',
        ]);
        $targeted->targetServiceProviders()->sync([$selectedProvider->id]);

        $this->actingAs($selectedProvider->user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee($broadcast->title_en)
            ->assertSee($targeted->title_en);

        $this->actingAs($otherProvider->user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee($broadcast->title_en)
            ->assertDontSee($targeted->title_en);
    }

    public function test_unread_count_only_counts_notifications_visible_to_current_provider(): void
    {
        $selectedProvider = $this->createProvider('Selected Provider');
        $otherProvider = $this->createProvider('Other Provider');

        AdminNotification::factory()->active()->createdBy($this->admin->id)->create();
        $targeted = AdminNotification::factory()->active()->createdBy($this->admin->id)->create();
        $targeted->targetServiceProviders()->sync([$selectedProvider->id]);

        $this->actingAs($selectedProvider->user)
            ->getJson(route('notifications.unread-count'))
            ->assertOk()
            ->assertJson(['count' => 2]);

        $this->actingAs($otherProvider->user)
            ->getJson(route('notifications.unread-count'))
            ->assertOk()
            ->assertJson(['count' => 1]);
    }

    public function test_provider_cannot_mark_invisible_targeted_notification_as_read(): void
    {
        $selectedProvider = $this->createProvider('Selected Provider');
        $otherProvider = $this->createProvider('Other Provider');

        $targeted = AdminNotification::factory()->active()->createdBy($this->admin->id)->create();
        $targeted->targetServiceProviders()->sync([$selectedProvider->id]);

        $this->actingAs($otherProvider->user)
            ->postJson(route('notifications.mark-as-read'), [
                'notification_id' => $targeted->id,
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'marked_ids' => [],
            ]);

        $this->assertDatabaseMissing('admin_notification_user', [
            'user_id' => $otherProvider->user_id,
            'admin_notification_id' => $targeted->id,
        ]);
    }

    public function test_targeted_notification_clears_only_selected_provider_nav_cache(): void
    {
        $selectedProvider = $this->createProvider('Selected Provider');
        $otherProvider = $this->createProvider('Other Provider');

        Cache::put("nav_notifications_{$selectedProvider->user_id}", 'stale-selected', 300);
        Cache::put("nav_notifications_{$otherProvider->user_id}", 'stale-other', 300);

        $this->actingAs($this->admin)
            ->post(route('admin.notifications.store'), $this->payload([
                'target_mode' => 'selected',
                'service_provider_ids' => [$selectedProvider->id],
            ]))
            ->assertRedirect(route('admin.notifications.index'));

        $this->assertFalse(Cache::has("nav_notifications_{$selectedProvider->user_id}"));
        $this->assertTrue(Cache::has("nav_notifications_{$otherProvider->user_id}"));
    }

    private function createProvider(string $companyName): ServiceProvider
    {
        $user = User::factory()->serviceProvider()->create([
            'name' => $companyName . ' Owner',
        ]);
        $location = Location::factory()->create([
            'city' => $companyName . ' Test City',
        ]);

        return ServiceProvider::factory()->create([
            'user_id' => $user->id,
            'location_id' => $location->id,
            'company_name' => $companyName,
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title_ar' => 'عنوان اختبار',
            'title_en' => 'Test notification',
            'title_fr' => 'Notification de test',
            'message_ar' => 'رسالة اختبارية لمقدم الخدمة.',
            'message_en' => 'A test message for a service provider.',
            'message_fr' => 'Un message de test pour un prestataire.',
            'target_mode' => 'all',
        ], $overrides);
    }
}
