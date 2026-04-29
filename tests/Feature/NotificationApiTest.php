<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\AdminMessageNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

/**
 * Tests for NotificationController API.
 */
class NotificationApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->muhitaji()->create();
    }

    private function seedNotifications(int $count = 3, bool $unread = true): void
    {
        for ($i = 0; $i < $count; $i++) {
            $this->user->notify(new AdminMessageNotification("Title $i", "Body $i"));
        }
        if (! $unread) {
            $this->user->unreadNotifications->markAsRead();
        }
    }

    public function test_index_returns_notifications_with_consistent_shape(): void
    {
        $this->seedNotifications(2);

        $r = $this->actingAs($this->user, 'sanctum')->getJson('/api/notifications');

        $r->assertOk()
            ->assertJsonPath('data.unread_count', 2)
            ->assertJsonPath('data.pagination.total', 2)
            ->assertJsonStructure([
                'data' => [
                    'notifications' => [['id', 'type', 'title', 'message', 'action_url', 'read', 'created_at']],
                    'unread_count',
                    'pagination',
                ],
            ]);
    }

    public function test_index_can_filter_unread_only(): void
    {
        $this->seedNotifications(3);
        $first = $this->user->notifications()->first();
        $first->markAsRead();

        $r = $this->actingAs($this->user, 'sanctum')->getJson('/api/notifications?filter=unread');
        $r->assertOk()->assertJsonPath('data.pagination.total', 2);
    }

    public function test_unread_count_endpoint(): void
    {
        $this->seedNotifications(5);

        $r = $this->actingAs($this->user, 'sanctum')->getJson('/api/notifications/unread-count');
        $r->assertOk()->assertJsonPath('data.unread_count', 5);
    }

    public function test_mark_single_as_read(): void
    {
        $this->seedNotifications(3);
        $id = $this->user->notifications()->first()->id;

        $r = $this->actingAs($this->user, 'sanctum')->postJson("/api/notifications/{$id}/read");
        $r->assertOk()->assertJsonPath('data.unread_count', 2);
        $this->assertNotNull(DatabaseNotification::find($id)->read_at);
    }

    public function test_mark_all_as_read(): void
    {
        $this->seedNotifications(4);

        $r = $this->actingAs($this->user, 'sanctum')->postJson('/api/notifications/read-all');
        $r->assertOk()
            ->assertJsonPath('data.unread_count', 0)
            ->assertJsonPath('data.marked_read', 4);
    }

    public function test_delete_single_notification(): void
    {
        $this->seedNotifications(2);
        $id = $this->user->notifications()->first()->id;

        $r = $this->actingAs($this->user, 'sanctum')->deleteJson("/api/notifications/{$id}");
        $r->assertOk();
        $this->assertNull(DatabaseNotification::find($id));
    }

    public function test_clear_all_notifications(): void
    {
        $this->seedNotifications(3);

        $r = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/notifications');
        $r->assertOk()->assertJsonPath('data.deleted', 3);
        $this->assertEquals(0, $this->user->notifications()->count());
    }

    public function test_user_cannot_access_other_users_notification(): void
    {
        $other = User::factory()->muhitaji()->create();
        $other->notify(new AdminMessageNotification('private', 'private'));
        $otherNotificationId = $other->notifications()->first()->id;

        $r = $this->actingAs($this->user, 'sanctum')->postJson("/api/notifications/{$otherNotificationId}/read");
        $r->assertStatus(404);
    }

    public function test_unauthenticated_user_cannot_access_notifications(): void
    {
        $r = $this->getJson('/api/notifications');
        $r->assertStatus(401);
    }
}
