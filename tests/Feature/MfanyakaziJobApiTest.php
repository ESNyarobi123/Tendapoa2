<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Tests for MfanyakaziJobController — worker as a job poster.
 */
class MfanyakaziJobApiTest extends TestCase
{
    use RefreshDatabase;

    private User $worker;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->worker = User::factory()->mfanyakazi()->create(['lat' => -6.8, 'lng' => 39.28]);
        $this->category = Category::factory()->create();
    }

    public function test_worker_can_post_a_free_job_with_image(): void
    {
        Storage::fake('public');
        \App\Models\Setting::set('job_posting_fee', 0);

        $response = $this->actingAs($this->worker, 'sanctum')->postJson('/api/worker/jobs', [
            'title' => 'Need help with garden',
            'category_id' => $this->category->id,
            'description' => 'Looking for someone to help me with my garden this weekend please.',
            'price' => 5000,
            'lat' => -6.8,
            'lng' => 39.28,
            'phone' => '0712345678',
            'address_text' => 'Mikocheni',
            'image' => UploadedFile::fake()->image('job.jpg', 800, 600),
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'data' => ['job' => ['id', 'title', 'price', 'image_url']]])
            ->assertJsonPath('payment_method', 'free');

        $this->assertDatabaseHas('work_orders', [
            'user_id' => $this->worker->id,
            'title' => 'Need help with garden',
            'poster_type' => 'mfanyakazi',
        ]);

        // Verify image was actually saved
        $job = Job::latest('id')->first();
        $this->assertNotNull($job->image);
    }

    public function test_worker_post_validation_rejects_bad_phone(): void
    {
        \App\Models\Setting::set('job_posting_fee', 0);

        $r = $this->actingAs($this->worker, 'sanctum')->postJson('/api/worker/jobs', [
            'title' => 'Job',
            'category_id' => $this->category->id,
            'description' => 'Need help with this thing please today.',
            'price' => 5000,
            'lat' => -6.8,
            'lng' => 39.28,
            'phone' => '12345', // invalid
        ]);

        $r->assertStatus(422);
    }

    public function test_muhitaji_cannot_use_worker_post_endpoint(): void
    {
        $muhitaji = User::factory()->muhitaji()->create();
        \App\Models\Setting::set('job_posting_fee', 0);

        $r = $this->actingAs($muhitaji, 'sanctum')->postJson('/api/worker/jobs', [
            'title' => 'Job', 'category_id' => $this->category->id,
            'description' => 'Need help with this thing please today.', 'price' => 5000,
            'lat' => -6.8, 'lng' => 39.28, 'phone' => '0712345678',
        ]);
        $r->assertStatus(403);
    }

    public function test_worker_can_list_their_posted_jobs(): void
    {
        Job::factory()->count(3)->create(['user_id' => $this->worker->id, 'poster_type' => 'mfanyakazi']);
        // Other worker's job (must NOT show up)
        $other = User::factory()->mfanyakazi()->create();
        Job::factory()->create(['user_id' => $other->id]);

        $r = $this->actingAs($this->worker, 'sanctum')->getJson('/api/worker/my-posted-jobs');

        $r->assertOk()
            ->assertJsonPath('data.pagination.total', 3)
            ->assertJsonStructure(['data' => ['jobs', 'pagination', 'counts']]);
    }

    public function test_worker_can_view_applications_received_on_their_job(): void
    {
        $job = Job::factory()->open()->create(['user_id' => $this->worker->id]);
        $applicantA = User::factory()->mfanyakazi()->create(['name' => 'Applicant A']);
        $applicantB = User::factory()->mfanyakazi()->create(['name' => 'Applicant B']);

        JobApplication::create([
            'work_order_id' => $job->id, 'worker_id' => $applicantA->id,
            'proposed_amount' => 4000, 'message' => 'I can do this', 'status' => 'applied',
        ]);
        JobApplication::create([
            'work_order_id' => $job->id, 'worker_id' => $applicantB->id,
            'proposed_amount' => 5000, 'message' => 'Pick me', 'status' => 'shortlisted',
            'shortlisted_at' => now(),
        ]);

        $r = $this->actingAs($this->worker, 'sanctum')
            ->getJson("/api/worker/jobs/{$job->id}/applications");

        $r->assertOk()
            ->assertJsonPath('data.pagination.total', 2)
            ->assertJsonStructure(['data' => ['job', 'applications' => [['id', 'status', 'worker' => ['id', 'name', 'phone']]]]]);
    }

    public function test_worker_cannot_view_applications_on_other_workers_job(): void
    {
        $other = User::factory()->mfanyakazi()->create();
        $job = Job::factory()->open()->create(['user_id' => $other->id]);

        $r = $this->actingAs($this->worker, 'sanctum')->getJson("/api/worker/jobs/{$job->id}/applications");
        $r->assertStatus(403);
    }

    public function test_worker_can_update_open_job(): void
    {
        $job = Job::factory()->open()->create(['user_id' => $this->worker->id, 'price' => 5000]);

        $r = $this->actingAs($this->worker, 'sanctum')->putJson("/api/worker/jobs/{$job->id}", [
            'price' => 7500,
        ]);

        $r->assertOk();
        $this->assertEquals(7500, $job->fresh()->price);
    }

    public function test_worker_cannot_update_job_after_worker_selected(): void
    {
        $other = User::factory()->mfanyakazi()->create();
        $job = Job::factory()->awaitingPayment()->create([
            'user_id' => $this->worker->id,
            'selected_worker_id' => $other->id,
        ]);

        $r = $this->actingAs($this->worker, 'sanctum')->putJson("/api/worker/jobs/{$job->id}", ['price' => 9999]);
        $r->assertStatus(422);
    }

    public function test_worker_can_cancel_open_job_and_applications_get_rejected(): void
    {
        $job = Job::factory()->open()->create(['user_id' => $this->worker->id]);
        $applicant = User::factory()->mfanyakazi()->create();
        JobApplication::create([
            'work_order_id' => $job->id, 'worker_id' => $applicant->id,
            'proposed_amount' => 4000, 'message' => 'me', 'status' => 'applied',
        ]);

        $r = $this->actingAs($this->worker, 'sanctum')->deleteJson("/api/worker/jobs/{$job->id}");
        $r->assertOk();

        $this->assertEquals(Job::S_CANCELLED, $job->fresh()->status);
        $this->assertDatabaseHas('job_applications', [
            'work_order_id' => $job->id, 'worker_id' => $applicant->id, 'status' => 'rejected',
        ]);
    }
}
