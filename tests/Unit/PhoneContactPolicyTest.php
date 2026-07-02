<?php

namespace Tests\Unit;

use App\Models\Job;
use App\Services\PhoneContactPolicy;
use Carbon\Carbon;
use Tests\TestCase;

class PhoneContactPolicyTest extends TestCase
{
    private function makeJob(array $overrides = []): Job
    {
        return Job::make(array_merge([
            'user_id' => 1,
            'accepted_worker_id' => 2,
            'status' => Job::S_IN_PROGRESS,
            'funded_at' => Carbon::now(),
            'accepted_by_worker_at' => Carbon::now(),
        ], $overrides));
    }

    public function test_blocks_phone_in_chat_before_escrow_and_acceptance(): void
    {
        $job = $this->makeJob([
            'funded_at' => null,
            'accepted_by_worker_at' => null,
            'status' => Job::S_OPEN,
        ]);

        $this->assertFalse($job->allowsPhoneSharingInChat(1, 2));
        $this->assertTrue(PhoneContactPolicy::userTextContainsBlockedPhone(
            'Nipigie 0712345678',
            'chat',
            $job,
            1,
            2,
        ));
    }

    public function test_allows_phone_in_chat_between_client_and_accepted_worker(): void
    {
        $job = $this->makeJob();

        $this->assertTrue($job->allowsPhoneSharingInChat(1, 2));
        $this->assertFalse(PhoneContactPolicy::userTextContainsBlockedPhone(
            'Nipigie 0712345678',
            'chat',
            $job,
            1,
            2,
        ));
    }

    public function test_always_blocks_phone_in_job_description_surface(): void
    {
        $job = $this->makeJob();

        $this->assertTrue(PhoneContactPolicy::userTextContainsBlockedPhone(
            '0712345678',
            'job_description',
            $job,
            1,
            null,
        ));
    }
}
